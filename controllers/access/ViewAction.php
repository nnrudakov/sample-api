<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\controllers\access;

use Yii;
use yii\caching\TagDependency;
use yii\db\pgsql\ArrayParser;
use yii\web\{ForbiddenHttpException, User as WebUser};
use app\controllers\{BaseAction, UsersController};
use app\controllers\exceptions\InvalidParamException;
use app\models\Access;
use app\models\db\User;

/**
 * Действие получения списка разрешений пользователя..
 *
 * Возвращает список разрешений пользователя в данной организации.
 *
 * @property WebUser $webUser Системный компонент пользователя.
 * @property User    $user    Текущий пользователь.
 *
 * @package    app\controllers\access
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class ViewAction extends BaseAction
{
    /**
     * @var WebUser Системный компонент пользователя.
     */
    public WebUser $webUser;
    /**
     * @var User Текущий пользователь.
     */
    public User $user;

    /**
     * @OA\Get(
     *     path="/access/{companyId}/{userId}",
     *     operationId="access-view",
     *     tags={"Разрешения"},
     *     summary="Получение разрешений пользователя",
     *     description="Возвращает список разрешений пользователя в данной организации. Список возможных разрешений описан в ТЗ. Запрос разрешений главного администратора запрещён даже самому главному администратору.",
     *     @OA\Parameter(
     *         name="companyId",
     *         in="path",
     *         description="Идентификатор организации",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         description="Идентификатор пользователя",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Разрешения пользователя в организации",
     *          @OA\JsonContent(ref="#/components/schemas/Access")
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Ошибка параметров",
     *          @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *          response="403",
     *          description="Доступ к запросу запрещён",
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
     * Запуск действия.
     *
     * @param integer $companyId Идентификатор организации.
     * @param integer $userId    Идентификатор пользователя.
     *
     * @return array Ключом является имя разрешения (из списка {@link \app\models\Access::$permissions}), значением
     *               булевый статус разрешения.
     *
     * @throws ForbiddenHttpException Если запрещено запрашивать действие
     * @throws InvalidParamException В организации пользователя нет
     */
    public function run(int $companyId, int $userId): array
    {
        $this->checkAccess($companyId, $userId);
        $modelClass = $this->modelClass;
        /** @var Access $access */
        $access = new $modelClass(['companyId' => $companyId, 'userId' => $userId]);

        return $access->getPermissions();
    }

    /** @noinspection ClassMethodNameMatchesFieldNameInspection */
    /**
     * Проверка доступа к действию если запрос от администратора.
     *
     * @param integer $companyId Идентификатор организации.
     * @param integer $userId    Идентификатор пользователя.
     *
     * @throws ForbiddenHttpException Если доступ запрещён
     * @throws InvalidParamException В организации пользователя нет
     */
    public function checkAccess(int $companyId, int $userId): void
    {
        $this->checkAccessForSuperAdmin($userId);
        if ($this->user->isAdmin()) {
            $this->checkAccessForOnlyUsers($userId);
            $this->checkAccessForManage($companyId);
        }
        $this->checkAccessUserCompany($companyId, $userId);
    }

    /**
     * Проверка доступа запроса разрешений главного администратора.
     *
     * @param integer $userId Идентификатор пользователя.
     *
     * @throws ForbiddenHttpException Если доступ запрещён
     */
    private function checkAccessForSuperAdmin(int $userId): void
    {
        if ($userId === User::SUPER_ID) {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

    /**
     * Проверка доступа запрещён разрешений других администраторов.
     *
     * @param integer $userId Идентификатор пользователя.
     *
     * @throws ForbiddenHttpException Если доступ запрещён
     */
    private function checkAccessForOnlyUsers(int $userId): void
    {
        $role = User::find()->select(['role'])->where(['id' => $userId])
            ->cache(2592000, new TagDependency(['tags' => UsersController::USERS_CACHE]))->scalar();
        if (empty($role) || $role === $this->user->role) {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

    /**
     * Проверка доступа запроса разрешений в данной организации.
     *
     * @param integer $companyId Идентификатор организации.
     *
     * @throws ForbiddenHttpException Если доступ запрещён
     */
    private function checkAccessForManage(int $companyId): void
    {
        if (!$this->webUser->can('manageUsers' . $companyId)) {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

    /**
     * Проверка доступа, чтобы пользователь быть ровно в той организации, по которой выполняется запрос.
     *
     * @param integer $companyId Идентификатор организации.
     * @param integer $userId    Идентификатор пользователя.
     *
     * @throws InvalidParamException В организации пользователя нет
     */
    private function checkAccessUserCompany(int $companyId, int $userId): void
    {
        $companies = User::find()->select(['companies'])->where(['id' => $userId])
            ->cache(2592000, new TagDependency(['tags' => UsersController::USERS_CACHE]))->scalar();
        $companies = \array_map('intval', (new ArrayParser())->parse($companies));
        if (!\in_array($companyId, $companies, true)) {
            throw new InvalidParamException('user_company_mismatch');
        }
    }
}
