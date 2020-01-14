<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\controllers\users;

use app\controllers\{ActionTrait, UsersController};
use app\models\db\User;

/**
 * Действие добавления нового пользователя.
 *
 * Получает новые данные пользователя и добавляет пользователя в систему. В случае успешного добавления пользователю
 * через поведение {@link \app\components\behaviors\PasswordSendBehavior} отправляется автоматически сгенерированный
 * пароль для входа.
 *
 * @package    app\controllers\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class CreateAction extends \yii\rest\CreateAction
{
    use ActionTrait;

    /**
     * @OA\Post(
     *     path="/users",
     *     operationId="users-create",
     *     tags={"Пользователи"},
     *     summary="Добавление пользователя",
     *     description="Получает новые данные пользователя и добавляет пользователя в систему. В случае успешного добавления пользователю отправляется автоматически сгенерированный пароль для входа.",
     *     @OA\Parameter(
     *         name="X-Csrf-Token",
     *         in="header",
     *         description="CSRF токен",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Новые данные",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"email", "name", "enabled", "role", "companies"},
     *                 @OA\Property(
     *                      property="email",
     *                      ref="#/components/schemas/email"
     *                 ),
     *                 @OA\Property(
     *                      property="name",
     *                      ref="#/components/schemas/name"
     *                 ),
     *                 @OA\Property(
     *                      property="enabled",
     *                      title="Активен",
     *                      type="boolean"
     *                 ),
     *                 @OA\Property(
     *                      property="role",
     *                      ref="#/components/schemas/role"
     *                 ),
     *                 @OA\Property(
     *                      property="companies",
     *                      title="Список организаций",
     *                      description="Указываются идентификаторы организаций, к которым будет иметь доступ пользователь.",
     *                      type="array",
     *                      @OA\Items(
     *                          type="integer",
     *                      )
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *          response="201",
     *          description="Пользователь успешно добавлен",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                      property="id",
     *                      ref="#/components/schemas/id"
     *                 ),
     *                 @OA\Property(
     *                      property="email",
     *                      ref="#/components/schemas/email"
     *                 ),
     *                 @OA\Property(
     *                      property="name",
     *                      ref="#/components/schemas/name"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Неверный CSRF токен",
     *          @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *          response="422",
     *          description="Ошибки валидации данных",
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
    public function run(): User
    {
        /** @var User $user */
        $user = parent::run();
        $this->logObject = $user->name;
        if (!$user->hasErrors()) {
            $this->invalidateCache(UsersController::USERS_CACHE);
        }

        return $user;
    }
}
