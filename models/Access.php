<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\base\Model;
use yii\caching\TagDependency;
use yii\rbac\DbManager;
use app\controllers\UsersController;
use app\models\db\User;

/**
 * @OA\Schema(
 *     schema="Access",
 *     title="Разрешения",
 *     @OA\Property(
 *          property="manageUsers",
 *          title="Управление пользователями",
 *          type="boolean"
 *     ),
 *     @OA\Property(
 *          property="viewDevices",
 *          title="Просмотр приборов учёта",
 *          type="boolean"
 *     ),
 *     @OA\Property(
 *          property="manageDevices",
 *          title="Управление приборами учёта",
 *          type="boolean"
 *     ),
 *     @OA\Property(
 *          property="viewEquipments",
 *          title="Просмотр оборудования",
 *          type="boolean"
 *     ),
 *     @OA\Property(
 *          property="manageEquipments",
 *          title="Управление оборудованием",
 *          type="boolean"
 *     ),
 *     @OA\Property(
 *          property="managePlacements",
 *          title="Управление помещениями",
 *          type="boolean"
 *     ),
 *     @OA\Property(
 *          property="manageLines",
 *          title="Управление линиями",
 *          type="boolean"
 *     ),
 *     @OA\Property(
 *          property="manageExtFactors",
 *          title="Управление внешними факторами",
 *          type="boolean"
 *     ),
 *     @OA\Property(
 *          property="enterMetrics",
 *          title="Ввод показателей",
 *          type="boolean"
 *     ),
 *     @OA\Property(
 *          property="manageTariffs",
 *          title="Управление тарифами",
 *          type="boolean"
 *     ),
 *     @OA\Property(
 *          property="viewEconomic",
 *          title="Просмотр экономических показателей",
 *          type="boolean"
 *     ),
 * )
 */
/**
 * Модель работы с разрешениями.
 *
 * Позволяет создать доступы организации, назначить их пользователям и получить список разрешений пользователя в
 * конкретной организации.
 *
 * Для каждого пользователя список его разрешений для каждой организации хранится в кеше и сбрасывается каждый раз при
 * изменении разрешений. Все кеши пользователя хранятся под общим тегом на случай добавления новых разрешений. В этом
 * случае все кеши делаются невалидными.
 *
 * Структура разрешений выглядит следующим образом:
 * ```text
 * - superAdmin
 *       |__ admin
 *       |__ user
 * - manageUsers1
 * - viewDevices1
 * - manageDevices1
 * - viewEquipments1
 * - manageEquipments1
 * - managePlacements1
 * - manageLines1
 * - manageExtFactors1
 * - enterMetrics1
 * - manageTariffs1
 * - viewEconomic1
 * ```
 *
 * `superAdmin`, `admin` и `user` являются ролями. Остальное разрешениями. Цифра в конце каждого разрешения означает
 * идентификатор организации. Сколько организаций, столько и наборов разрешений.
 *
 * @property integer $companyId Идентификатор организации.
 * @property integer $userId    Идентификатор пользователя.
 *
 * @package    app\models
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class Access extends Model
{
    /**
     * @var string Имя ключа кеша.
     */
    public const CACHE = 'access';

    /**
     * @var integer Идентификатор организации.
     */
    public int $companyId;
    /**
     * @var integer Идентификатор пользователя.
     */
    public int $userId;

    /**
     * @var DbManager Компонент ролевой модели RBAC.
     */
    private DbManager $auth;

    /**
     * @var array Список разрешений.
     */
    private static array $permissions = [
        'manageUsers'      => 'Управление пользователями',
        'viewDevices'      => 'Просмотр приборов учёта',
        'manageDevices'    => 'Управление приборами учёта',
        'viewEquipments'   => 'Просмотр оборудования',
        'manageEquipments' => 'Управление оборудованием',
        'managePlacements' => 'Управление помещениями',
        'manageLines'      => 'Управление линиями',
        'manageExtFactors' => 'Управление внешними факторами',
        'enterMetrics'     => 'Ввод показателей',
        'manageTariffs'    => 'Управление тарифами',
        'viewEconomic'     => 'Просмотр экономических показателей',
    ];

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();

        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->auth = Yii::$app->authManager;
    }

    /**
     * Создание разрешений для новой организации.
     *
     * Для каждой организации создаётся свой собственный список разрешений. К имени разрешения добавляется идентификатор
     * организации.
     */
    public function createCompanyPermissions(): void
    {
        foreach (static::$permissions as $name => $description) {
            $permission = $this->auth->createPermission($name . $this->companyId);
            $permission->description = $description;
            $this->auth->add($permission);
        }
    }

    /**
     * Присвоение роли пользователю.
     *
     * @param string $role Роль.
     *
     * @see \app\components\behaviors\AssignRoleBehavior
     */
    public function assignRole(string $role): void
    {
        $this->auth->assign($this->auth->getRole($role), $this->userId);
    }

    /**
     * Удаление разрешений пользователя для всех переданных организаций.
     *
     * @param array $companies Список идентификаторов организаций.
     *
     * @see \app\components\behaviors\ResetPermissionsBehavior
     */
    public function resetPermissions(array $companies): void
    {
        foreach ($companies as $id) {
            foreach (\array_keys(static::$permissions) as $name) {
                if ($permission = $this->auth->getPermission($name . $id)) {
                    $this->auth->revoke($permission, $this->userId);
                }
            }
        }
    }

    /**
     * Возвращает список разрешений пользователя.
     *
     * @return array Ключом является имя разрешения (из списка {@link Access::$permissions}), значением булевый статус
     *               разрешения.
     */
    public function getPermissions(): array
    {
        return Yii::$app->cache->getOrSet(
            static::CACHE . "_{$this->companyId}_{$this->userId}",
            function () {
                $is_user = $this->isRoleUser();
                $permissions = [];
                foreach (\array_keys(static::$permissions) as $permission) {
                    if ($is_user && $permission === 'manageUsers') {
                        continue;
                    }
                    $permissions[$permission] = $this->auth->checkAccess($this->userId, $permission . $this->companyId);
                }

                return $permissions;
            },
            2592000,
            new TagDependency(['tags' => static::CACHE])
        );
    }

    /**
     * Установка разрешений пользователя.
     *
     * @param array $permissions Ключом является имя разрешения (из списка {@link Access::$permissions}), значением
     *                           булевый статус разрешения.
     */
    public function setPermissions(array $permissions): void
    {
        $is_user = $this->isRoleUser();
        $current_permissions = $this->getPermissions();
        foreach (\array_keys(static::$permissions) as $permission) {
            if ($is_user && $permission === 'manageUsers') {
                continue;
            }

            $permission_object = $this->auth->getPermission($permission . $this->companyId);
            if (!isset($permissions[$permission])) {
                $this->auth->revoke($permission_object, $this->userId);
            } elseif ($current_permissions[$permission] === (bool) $permissions[$permission]) {
                continue;
            } else {
                $this->auth->revoke($permission_object, $this->userId);
                if ($permissions[$permission]) {
                    $this->auth->assign($permission_object, $this->userId);
                }
            }
        }
        $this->invalidateCache([$this->companyId]);
    }

    /**
     * Очистка кеша разрешений пользователя в организациях.
     *
     * @param array $companies Список организаций.
     */
    public function invalidateCache(array $companies): void
    {
        $cache = Yii::$app->cache;
        foreach ($companies as $id) {
            $cache->delete(static::CACHE . "_{$id}_{$this->userId}");
        }
    }

    /**
     * Является ли запрашиваемый пользователь обычным.
     *
     * @return bool
     */
    private function isRoleUser(): bool
    {
        $role = User::find()->select(['role'])->where(['id' => $this->userId])
            ->cache(2592000, new TagDependency(['tags' => UsersController::USERS_CACHE]))->scalar();

        return $role === User::ROLE_USER;
    }
}
