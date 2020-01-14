<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\filters\{AjaxFilter, Cors};
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;
use yii\web\{Request, Response, Session, User as WebUser};
use app\models\User;
use app\models\db\UserLog;

/**
 * @OA\Info(title="SAMPLE API", version="1.0.0")
 *
 * @OA\Schema(
 *      schema="Attributes",
 *      title="Выбор атрибутов по типам",
 *      @OA\Property(
 *          property="fields",
 *          title="Атрибуты по умолчанию",
 *          description="Список атрибутов через запятую.",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="expand",
 *          title="Расширенные атрибуты",
 *          description="Список атрибутов через запятую.",
 *          type="string"
 *      )
 * )
 *
 * @OA\Schema(
 *      schema="List",
 *      title="Параметры для списков",
 *      @OA\Property(
 *          property="sort",
 *          title="Сортировка",
 *          description="Атрибут и направление сортировки",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="page",
 *          title="Номер страницы пагинации",
 *          type="ingeger",
 *          format="int32",
 *          default=1,
 *      ),
 *      @OA\Property(
 *          property="per-page",
 *          title="Количество элементов на странице",
 *          type="integer",
 *          format="int32",
 *          default=20,
 *      ),
 * )
 *
 * @OA\Header(
 *      @OA\Schema(type="integer"),
 *      header="X-Pagination-Total-Count",
 *      description="Общее число элементов."
 * )
 * @OA\Header(
 *      @OA\Schema(type="integer"),
 *      header="X-Pagination-Page-Count",
 *      description="Количество страниц, исходя из заданного параметра `per-page`."
 * )
 * @OA\Header(
 *      @OA\Schema(type="integer"),
 *      header="X-Pagination-Current-Page",
 *      description="Номер текущей страницы (параметр `page`). Нумерация начинается с 1."
 * )
 * @OA\Header(
 *      @OA\Schema(type="integer"),
 *      header="X-Pagination-Per-Page",
 *      description="Количество элементов на странице (параметр `per-page`)."
 * )
 * @OA\Header(
 *      @OA\Schema(type="string"),
 *      header="Link",
 *      description="Набор навигационных ссылок."
 * )
 *
 * @OA\Schema(
 *     schema="id",
 *     title="Идентификатор",
 *     type="integer",
 *     format="int64",
 * )
 * @OA\Schema(
 *     schema="title",
 *     title="Наименование",
 *     type="string"
 * )
 * @OA\Schema(
 *     schema="comment",
 *     title="Комментарий",
 *     type="string"
 * )
 * @OA\Schema(
 *     schema="role",
 *     title="Роль",
 *     description="Возможные роли:<br>`admin` — Администратор;<br>`user` — Пользователь.",
 *     type="string",
 *     enum={"admin", "user"}
 * ),
 */
/**
 * Базовый контроллер, который должны наследовать все контроллеры.
 *
 * Выполняет служебные операции перед и после выполнения запроса. В частности записывает лог действий пользователей.
 *
 * @package    app\controllers
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
abstract class BaseController extends ActiveController
{
    /**
     * {@inheritdoc}
     */
    public $enableCsrfValidation = true;

    /**
     * @var Request Объект запроса.
     */
    protected Request $request;
    /**
     * @var Response Объект ответа.
     */
    protected Response $response;
    /**
     * @var WebUser Системный компонент пользователя.
     */
    protected WebUser $webUser;
    /**
     * @var User Текущий пользователь.
     */
    protected ?User $user = null;
    /**
     * @var Session Сессия.
     */
    protected Session $session;

    /**
     * @var bool Запрос на запись данных.
     */
    private bool $isWriteRequest;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        $request = $this->request = Yii::$app->getRequest();
        $this->response = Yii::$app->getResponse();
        $this->webUser = Yii::$app->getUser();
        $this->session = Yii::$app->getSession();
        $this->user = $this->webUser->getIdentity();
        $this->isWriteRequest = $request->getIsPost() || $request->getIsPatch() || $request->getIsDelete();
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'corsFilter' => [
                'class' => Cors::class,
                'cors'  => Yii::$app->params['cors']
            ],
            'ajax' => [
                'class' => AjaxFilter::class
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        $actions = parent::actions();
        unset($actions['options']);

        return $actions;
    }

    /**
     * {@inheritdoc}
     */
    public function afterAction($action, $result)
    {
        $this->logAction($action);
        $this->regenerateCsrfToken();

        return parent::afterAction($action, $result);
    }

    /**
     * Журналирование действий пользователя.
     *
     * @param BaseAction|\yii\base\Action $action Действие.
     */
    private function logAction($action): void
    {
        $user_id = $this->webUser->getId() ?: $action->getLogUserId();
        if ($user_id && $this->isWriteRequest) {
            $model             = new UserLog();
            $model->user_id    = $user_id;
            $model->company_id = $action->getLogCompanyId() ?: null;
            $model->action     = $action->getLogActionId() ?: $this->id . '/' . $action->id;
            $model->object_id  = $action->getLogObject() ?: ArrayHelper::getValue($this->actionParams, 'id');
            $model->ip         = $this->request->userIP;
            $model->client     = $this->request->userAgent;
            if (!$model->save()) {
                static::log('Errors while save log record' . print_r($model->errors, true), 'error');
            }
        }
    }

    /**
     * Генерация нового CSRF токена.
     */
    private function regenerateCsrfToken(): void
    {
        if ($this->isWriteRequest) {
            $this->request->getCsrfToken(true);
        }
    }

    /**
     * Журналирование сообщений в отдельную категорию приложения.
     *
     * @param string $message Сообщение.
     * @param string $type    Тип: информация (info), предупреждение (warning), ошибка (error). Является методом
     *                        стандартного логгера.
     *
     * @see Yii::info()
     * @see Yii::warning()
     * @see Yii::error()
     */
    public static function log(string $message, string $type = 'info'): void
    {
        Yii::$type($message, 'sample');
    }
}
