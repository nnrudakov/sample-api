<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\controllers\access;

use app\controllers\exceptions\InvalidParamException;
use app\models\Access;

/**
 * Действие установки разрешений пользователя.
 *
 * Устанавливает разрешения пользователя в данной организации.
 *
 * @package    app\controllers\access
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class UpdateAction extends ViewAction
{
    /**
     * @var array Разрешения пользователя.
     */
    private array $permissions;

    /**
     * {@inheritdoc}
     */
    protected function beforeRun(): bool
    {
        $permissions = $this->request->getBodyParam('permissions');
        if (empty($permissions)) {
            throw new InvalidParamException('access_empty');
        }
        $this->permissions = $permissions;

        return parent::beforeRun();
    }

    /**
     * @OA\Schema(
     *     schema="AccessUpdateRequest",
     *     title="Новые разрешения",
     *     required={"permissions"},
     *     @OA\Property(
     *          property="permissions",
     *          title="Список разрешений",
     *          required={"manageUsers", "viewDevices", "manageDevices", "viewEquipments", "manageEquipments", "managePlacements", "manageLines", "manageExtFactors", "enterMetrics", "manageTariffs", "viewEconomic"},
     *          allOf={
     *              @OA\Schema(ref="#/components/schemas/Access")
     *          }
     *     )
     * )
     *
     * @OA\Patch(
     *     path="/access/{companyId}/{userId}",
     *     operationId="access-update",
     *     tags={"Разрешения"},
     *     summary="Установка разрешений пользователя",
     *     description="Устанавливает разрешения пользователя в данной организации. Список возможных разрешений описан в ТЗ. Установка разрешений главного администратора запрещена даже самому главному администратору.",
     *     @OA\Parameter(
     *         name="X-Csrf-Token",
     *         in="header",
     *         description="CSRF токен",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
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
     *     @OA\RequestBody(
     *         description="Новые разрешения",
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/AccessUpdateRequest")
     *     ),
     *     @OA\Response(
     *          response="204",
     *          description="Разрешения пользователя в организации успешно установлены",
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
     * {@inheritdoc}
     */
    public function run(int $companyId, int $userId): array
    {
        $this->checkAccess($companyId, $userId);
        $modelClass = $this->modelClass;
        /** @var Access $access */
        $access = new $modelClass(['companyId' => $companyId, 'userId' => $userId]);
        $access->setPermissions($this->permissions);
        $this->response->setStatusCode(204, 'Access Has Been Updated.');

        return [];
    }
}
