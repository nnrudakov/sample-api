<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\controllers\users;

use Yii;
use yii\caching\TagDependency;
use yii\data\{ActiveDataFilter, ActiveDataProvider};
use yii\web\{User as WebUser, ForbiddenHttpException};
use app\controllers\{ActionTrait, UsersController};
use app\controllers\exceptions\InvalidParamException;
use app\models\db\User;

/**
 * Действие получения списка пользователей.
 *
 * Возвращает список пользователей с учётом параметров фильтрации, сортировки и пагинации.
 *
 * @property WebUser $webUser Системный компонент пользователя.
 * @property User    $user    Текущий пользователь.
 *
 * @package    app\controllers\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class IndexAction extends \yii\rest\IndexAction
{
    use ActionTrait;

    /**
     * @var WebUser Системный компонент пользователя.
     */
    public WebUser $webUser;
    /**
     * @var User Текущий пользователь.
     */
    public User $user;

    /** @var string Выбираемая роль пользователей */
    private string $role;
    /** @var int Идентификатор организации */
    private int $companyId;

    /**
     * {@inheritdoc}
     *
     * @return ActiveDataProvider|ActiveDataFilter
     */
    public function init(): void
    {
        parent::init();

        $this->checkAccess = [$this, 'checkAccess'];
    }

    /**
     * @OA\Schema(
     *     schema="UsersFilter",
     *     title="Фильтр пользователей",
     *     required={"role", "companies"},
     *     @OA\Property(
     *         property="role",
     *         ref="#/components/schemas/role"
     *     ),
     *     @OA\Property(
     *         property="companies",
     *         title="Идентификатор организации",
     *         type="integer",
     *         format="int32",
     *     ),
     * )
     *
     * @OA\Schema(
     *     schema="UsersFilterRequest",
     *     allOf={
     *          @OA\Schema(ref="#/components/schemas/Attributes"),
     *          @OA\Schema(ref="#/components/schemas/List"),
     *          @OA\Schema(
     *              required={"filter"},
     *              title="Фильтр пользователей",
     *              @OA\Property(
     *                  property="filter",
     *                  ref="#/components/schemas/UsersFilter"
     *              )
     *          )
     *     }
     * )
     *
     * @OA\Get(
     *     path="/users",
     *     operationId="users-get-list",
     *     tags={"Пользователи"},
     *     summary="Получение списка пользователей",
     *     description="Возвращает список пользователей. Список возвращается в соответствии с правилами доступа.<br><br>См. [работу со списком](#section/Spiski). Сортировка по умолчанию по дате добавления в обратном порядке.<br><br>Список доступных атрибутов см. в [информации о пользователе](#operation/users-get-one).",
     *     @OA\RequestBody(
     *         description="Фильтр",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UsersFilterRequest")
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Список пользователей",
     *          @OA\Header(
     *              header="X-Pagination-Total-Count",
     *              ref="#/components/headers/X-Pagination-Total-Count"
     *          ),
     *          @OA\Header(
     *              header="X-Pagination-Page-Count",
     *              ref="#/components/headers/X-Pagination-Page-Count"
     *          ),
     *          @OA\Header(
     *              header="X-Pagination-Current-Page",
     *              ref="#/components/headers/X-Pagination-Current-Page"
     *          ),
     *          @OA\Header(
     *              header="X-Pagination-Per-Page",
     *              ref="#/components/headers/X-Pagination-Per-Page"
     *          ),
     *          @OA\Header(
     *              header="Link",
     *              ref="#/components/headers/Link"
     *          ),
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/UserShort")
     *          ),
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Ошибки парметров",
     *          @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *          response="422",
     *          description="Ошибки валидации фильтра",
     *          @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ValidateError")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Ошибка",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    /**
     * {@inheritdoc}
     */
    protected function beforeRun(): bool
    {
        $filter = $this->request->get('filter');
        if (empty($filter['role'])) {
            throw new InvalidParamException('role_empty');
        }
        if (empty($filter['companies'])) {
            throw new InvalidParamException('company_empty');
        }

        $this->role = $filter['role'];
        $this->companyId = (int) $filter['companies'];

        return true;
    }

    /** @noinspection ClassMethodNameMatchesFieldNameInspection */
    /**
     * {@inheritdoc}
     *
     * @return ActiveDataProvider|ActiveDataFilter
     */
    protected function prepareDataProvider()
    {
        $provider = parent::prepareDataProvider();
        if ($provider instanceof ActiveDataFilter) {
            return $provider;
        }
        $provider->query->select(['id', 'email', 'name', 'enabled']);
        if ($this->user->isAdmin()) {
            $provider->query->andWhere(['in', 'companies', $this->user->companies]);
        }
        $provider->query->cache(2592000, new TagDependency(['tags' => UsersController::USERS_CACHE]));
        $provider->sort->defaultOrder = ['id' => \SORT_DESC];

        return $provider;
    }

    /** @noinspection ClassMethodNameMatchesFieldNameInspection */
    /**
     * Проверка разрешений для получения списка.
     *
     * @throws ForbiddenHttpException Если доступ запрещён
     */
    protected function checkAccess(): void
    {
        if ($this->user->isAdmin()) {
            $this->checkAccessAdmin();
            $this->checkAccessUser();
        }
    }

    /**
     * Проверка доступа списка администраторов.
     *
     * @throws ForbiddenHttpException Если доступ запрещён
     */
    private function checkAccessAdmin(): void
    {
        if ($this->role === User::ROLE_ADMIN) {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

    /**
     * Проверка разрешения просмотра списка пользователей.
     *
     * @throws ForbiddenHttpException Если доступ запрещён
     */
    private function checkAccessUser(): void
    {
        if (!$this->webUser->can('manageUsers' . $this->companyId)) {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }
}
