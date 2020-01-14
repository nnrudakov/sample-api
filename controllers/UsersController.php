<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\filters\{AccessControl, VerbFilter};
use yii\caching\TagDependency;
use yii\data\ActiveDataFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use app\controllers\exceptions\{InvalidParamException, NotFoundException};
use app\controllers\users\{
    CreateAction,
    IndexAction,
    LoginAction,
    LogoutAction,
    PasswordResetRequestAction,
    PasswordResetAction,
    UpdateAction
};
use app\models\db\User;
use app\models\search\UserSearch;

/**
 * @OA\Tag(
 *     name="Пользователи",
 *     description="Операции с пользователями",
 * )
 *
 * @OA\Get(
 *     path="/users/{id}",
 *     operationId="users-get-one",
 *     tags={"Пользователи"},
 *     summary="Получение информации о пользователе",
 *     description="Возвращает информацию о пользователе по его идентификатору.

Атрибуты по типам ([см. использование](#section/Poluchenie-atributov-sushnostej)):

| Атрибут | По умолчанию | Расширенные  |
|---------|:------------:|:------------:|
| id      | *            |              |
| email   | *            |              |
| name    | *            |              |
| enabled |              | *            |
| role    |              | *            |",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Идентификатор пользователя",
 *         required=true,
 *         @OA\Schema(
 *             type="integer"
 *         )
 *     ),
 *     @OA\RequestBody(
 *         description="Выбор атрибутов по типам",
 *         required=false,
 *         @OA\JsonContent(ref="#/components/schemas/Attributes")
 *     ),
 *     @OA\Response(
 *          response="200",
 *          description="Данные пользователя",
 *          @OA\JsonContent(ref="#/components/schemas/User")
 *     ),
 *     @OA\Response(
 *          response="404",
 *          description="Пользователь не найден",
 *          @OA\JsonContent(ref="#/components/schemas/Error")
 *     ),
 *     @OA\Response(
 *         response="500",
 *         description="Ошибка",
 *         @OA\JsonContent(ref="#/components/schemas/Error")
 *     )
 * )
 */
/**
 * Контроллер управления пользователями.
 *
 * @package    app\controllers
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class UsersController extends BaseController
{
    /**
     * @var string Ключ тега кеша запросов к БД по пользователям.
     */
    public const USERS_CACHE = 'users';

    /**
     * {@inheritdoc}
     */
    public $modelClass = User::class;

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['password-reset-request', 'password-reset', 'login'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'view', 'update'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['create', 'index'],
                        'allow' => true,
                        'roles' => [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'password-reset-request' => ['POST'],
                    'password-reset' => ['POST'],
                    'login' => ['POST'],
                    'logout' => ['POST'],
                    'view' => ['GET'],
                    'update' => ['PATCH'],
                    'create' => ['POST'],
                    'index' => ['GET'],
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        $actions = parent::actions();
        unset($actions['delete']);
        $actions['view']['findModel'] = $actions['update']['findModel'] = [$this, 'findModel'];
        $actions['update']['class'] = UpdateAction::class;
        $actions['update']['response'] = $this->response;
        $actions['update']['user'] = $this->user;
        $actions['create']['class'] = CreateAction::class;
        $actions['index']['class'] = IndexAction::class;
        $actions['index']['modelClass'] = UserSearch::class;
        $actions['index']['request'] = $this->request;
        $actions['index']['webUser'] = $this->webUser;
        $actions['index']['user'] = $this->user;
        $actions['index']['dataFilter'] = ['class' => ActiveDataFilter::class, 'searchModel' => UserSearch::class];
        $actions['password-reset-request'] = [
            'class' => PasswordResetRequestAction::class,
            'modelClass' => $this->modelClass,
            'request' => $this->request,
            'response' => $this->response
        ];
        $actions['password-reset'] = [
            'class' => PasswordResetAction::class,
            'modelClass' => $this->modelClass,
            'request' => $this->request,
            'response' => $this->response
        ];
        $actions['login'] = [
            'class' => LoginAction::class,
            'modelClass' => $this->modelClass,
            'request' => $this->request,
            'webUser' => $this->webUser,
        ];
        $actions['logout'] = [
            'class' => LogoutAction::class,
            'modelClass' => $this->modelClass,
            'response' => $this->response,
            'webUser' => $this->webUser,
        ];

        return $actions;
    }

    /**
     * Поиск пользователя по идентификатору.
     *
     * Используется действиями при выборе пользователя. Введена выборка только необходимых полей, чтобы не было
     * возможности в публичной части приложения получить, например, хеш пароля.
     *
     * @param integer $id Идентификатор пользователя.
     *
     * @return User
     *
     * @throws NotFoundException Если пользователь не найден
     */
    public function findModel(int $id): User
    {
        $user = User::find()->select(['id', 'email', 'name', 'enabled', 'role', 'companies'])->where(['id' => $id])
            ->cache(86400, new TagDependency(['tags' => static::USERS_CACHE]))->one();
        if (!$user) {
            throw new NotFoundException('user_not_found');
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     *
     * @throws ForbiddenHttpException Если доступ запрещён
     * @throws InvalidParamException Если в параметрах не указан список организаций
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if (!$this->user->isSuperAdmin()) {
            if ($action === 'create') {
                $this->checkAccessCreate();
            }
            if ($action === 'update') {
                /** @var User $model */
                $this->checkAccessUpdate($model);
            }
            if ($action === 'view') {
                /** @var User $model */
                $this->checkAccessView($model);
            }
        }
    }

    /**
     * Проверка доступа для создания пользователя.
     *
     * @throws ForbiddenHttpException Если доступ запрещён
     * @throws InvalidParamException Если в параметрах не указан список организаций
     */
    private function checkAccessCreate(): void
    {
        $this->checkManageUsers($this->getCompaniesParam());
    }

    /**
     * Проверка доступа для обновления пользователя.
     *
     * @param User $model Пользователь, чьи данные обновляются.
     *
     * @throws ForbiddenHttpException Если доступ запрещён
     */
    private function checkAccessUpdate(User $model): void
    {
        $this->checkAccessSuperAdmin($model);
        if ($model->id !== $this->user->id) {
            $this->checkAccessAdmin($model);
            $this->checkAccessUpdateUser();
        }
    }

    /**
     * Проверка доступа для просмотра пользователя.
     *
     * @param User $model Пользователь, чьи данные просматриваются.
     *
     * @throws ForbiddenHttpException Если доступ запрещён
     */
    private function checkAccessView(User $model): void
    {
        $this->checkAccessSuperAdmin($model);
        if ($model->id !== $this->user->id) {
            $this->checkAccessAdmin($model);
            $this->checkAccessViewUser($model);
        }
    }

    /**
     * Проверка доступа к данным главного администратора.
     *
     * @param User $model Пользователь, чьи данные затрагиваются.
     *
     * @throws ForbiddenHttpException Данные главного администратора недоступны.
     */
    private function checkAccessSuperAdmin(User $model): void
    {
        if ($model->id === User::SUPER_ID) {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

    /**
     * Проверка доступа администратора к данным других администраторов.
     *
     * @param User $model Пользователь, чьи данные затрагиваются.
     *
     * @throws ForbiddenHttpException Если доступ запрещён
     */
    private function checkAccessAdmin(User $model): void
    {
        if ($model->isAdmin() && $this->user->isAdmin()) {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

    /**
     * Проверка доступа к обновлению данных пользователя.
     *
     * @throws ForbiddenHttpException Если доступ запрещён
     */
    private function checkAccessUpdateUser(): void
    {
        if ($this->user->isAdmin()) {
            try {
                $companies = $this->getCompaniesParam();
            } catch (InvalidParamException $e) {
                return;
            }
            $this->checkManageUsers($companies);
        } else {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

    /**
     * Проверка доступа для просмотра данных пользователя.
     *
     * @param User $model Пользователь, чьи данные затрагиваются.
     *
     * @throws ForbiddenHttpException Если доступ запрещён
     */
    private function checkAccessViewUser(User $model): void
    {
        if ($this->user->isAdmin()) {
            $this->checkManageUsers($model->companies->getValue());
        } else {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

    /**
     * Возвращает список организаций из запроса.
     *
     * @return array
     *
     * @throws InvalidParamException Если параметра нет в запросе
     */
    private function getCompaniesParam(): array
    {
        $companies = $this->request->getBodyParam('companies');
        if (!$companies) {
            throw new InvalidParamException('company_empty');
        }

        return $companies;
    }

    /**
     * Проверка разрешения на управление пользователями в организации.
     *
     * @param array $companies Список организаций.
     *
     * @throws ForbiddenHttpException Если разрешения управления пользователями нет
     */
    private function checkManageUsers(array $companies): void
    {
        if (!$this->webUser->can('manageUsers' . $companies[0])) {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }
}
