<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\filters\{AccessControl, VerbFilter};
use yii\caching\TagDependency;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use app\controllers\companies\{IndexAction, CreateAction, UpdateAction};
use app\models\db\{Company, User};

/**
 * @OA\Tag(
 *     name="Организации",
 *     description="Операции с организациями",
 * )
 *
 * @OA\Get(
 *     path="/companies/{id}",
 *     operationId="companies-get-one",
 *     tags={"Организации"},
 *     summary="Получение информации об организации",
 *     description="Возвращает информацию об организации по её идентификатору.

Атрибуты по типам ([см. использование](#section/Poluchenie-atributov-sushnostej)):

| Атрибут        | По умолчанию  | Расширенные  |
|----------------|:-------------:|:------------:|
| id             | *             |              |
| title          | *             |              |
| inn            | *             |              |
| ogrn           |               | *            |
| address        | *             |              |
| phone          | *             |              |
| person         | *             |              |
| contact        | *             |              |
| enabled        | *             |              |
| comment        |               | *            |
| scada_host     |               | *            |
| scada_port     |               | *            |
| scada_db       |               | *            |
| scada_user     |               | *            |
| scada_password |               | *            |",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="Идентификатор организации",
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
 *          description="Данные организации",
 *          @OA\JsonContent(ref="#/components/schemas/CompanyFull")
 *     ),
 *     @OA\Response(
 *          response="404",
 *          description="Организация не найдена",
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
 * Контроллер управления организациями.
 *
 * @package    app\controllers
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class CompaniesController extends BaseController
{
    /**
     * @var string Ключ тега кеша запросов к БД по организациям.
     */
    public const COMPANIES_CACHE = 'companies';

    /**
     * {@inheritdoc}
     */
    public $modelClass = Company::class;

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
                        'actions' => ['index', 'view'],
                        'allow' => true,
                        'roles' => [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN],
                    ],
                    [
                        'actions' => ['create', 'update'],
                        'allow' => true,
                        'roles' => [User::ROLE_SUPER_ADMIN],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['GET'],
                    'create' => ['POST'],
                    'view' => ['GET'],
                    'update' => ['PATCH'],
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
        $actions['index']['class'] = IndexAction::class;
        $actions['index']['isSuperAdmin'] = $this->user->isSuperAdmin();
        $actions['create']['class'] = CreateAction::class;
        $actions['view']['findModel'] = $actions['update']['findModel'] = [$this, 'findModel'];
        $actions['update']['class'] = UpdateAction::class;
        $actions['update']['response'] = $this->response;

        return $actions;
    }

    /**
     * Поиск организации по идентификатору.
     *
     * Используется действиями при выборе организации. Введена выборка только необходимых полей.
     *
     * @param integer $id Идентификатор организации.
     *
     * @return Company|null
     */
    public function findModel(int $id): ?Company
    {
        return Company::find()
            ->select([
                'id', 'title', 'inn', 'ogrn', 'address', 'phone', 'person', 'contact', 'enabled', 'person', 'contact',
                'scada_host', 'scada_port', 'scada_db', 'scada_user', 'scada_password', 'comment'
            ])
            ->where(['id' => $id])
            ->cache(2592000, new TagDependency(['tags' => static::COMPANIES_CACHE]))
            ->one();
    }
}
