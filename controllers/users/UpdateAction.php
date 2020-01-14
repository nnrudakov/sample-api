<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\controllers\users;

use yii\db\ArrayExpression;
use app\controllers\{ActionTrait, UsersController};
use app\models\Access;
use app\models\db\User;

/**
 * Действие обновления данных пользователя.
 *
 * Получает новые данные пользователя и обновляет.
 *
 * @property User $user Текущий пользователь.
 *
 * @package    app\controllers\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class UpdateAction extends \yii\rest\UpdateAction
{
    use ActionTrait;

    /**
     * @var User Текущий пользователь.
     */
    public User $user;

    /**
     * @OA\Patch(
     *     path="/users/{id}",
     *     operationId="users-update",
     *     tags={"Пользователи"},
     *     summary="Обновление пользователя",
     *     description="Получает новые данные пользователя и обновляет.",
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
     *         name="id",
     *         in="path",
     *         description="Идентификатор пользователя",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Новые данные",
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                      property="email",
     *                      ref="#/components/schemas/email"
     *                 ),
     *                 @OA\Property(
     *                      property="password",
     *                      title="Новый пароль",
     *                      description="Разрешается использовать только буквы английского алфавита, цифры и спец. символы. Минимум 6 символов.",
     *                      type="string",
     *                      format="password"
     *                 ),
     *                 @OA\Property(
     *                      property="password_repeat",
     *                      title="Повторение нового пароля",
     *                      description="Разрешается использовать только буквы английского алфавита, цифры и спец. символы. Минимум 6 символов.",
     *                      type="string",
     *                      format="password"
     *                 ),
     *                 @OA\Property(
     *                      property="name",
     *                      ref="#/components/schemas/name"
     *                 ),
     *                 @OA\Property(
     *                      property="enabled",
     *                      title="Активен",
     *                      description="Игнорируется если происходит обновление главного администратора.",
     *                      type="boolean"
     *                 ),
     *                 @OA\Property(
     *                      property="role",
     *                      title="Роль",
     *                      description="Игнорируется если происходит обновление главного администратора.",
     *                      type="string"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *          response="204",
     *          description="Данные успешно изменены"
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Неверный CSRF токен",
     *          @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Пользователь не найден",
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
    public function run($id): User
    {
        $this->setScenario((int) $id);
        /** @var User $user */
        $user = parent::run($id);
        $this->logObject = $user->name;
        if (!$user->hasErrors()) {
            $this->invalidateCache(UsersController::USERS_CACHE);
            $companies = $user->companies instanceof ArrayExpression ? $user->companies->getValue() : $user->companies;
            (new Access(['userId' => $user->id]))->invalidateCache($companies ?: []);
        }
        $this->response->setStatusCode(204, 'User Has Been Updated');

        return $user;
    }

    /**
     * Установка сценария обновления.
     *
     * Если пользователь обновляет сам себя, то можно изменять пароль, но нельзя менять роль и список компаний. Если
     * главный администратор обновляет пользователя, то можно менять всё, кроме пароля. В остальных случаях пользователя
     * обновляет администратор, которому можно менять всё, кроме роли и пароля.
     *
     * @param integer $id Идентификатор обновляемого пользователя.
     */
    private function setScenario(int $id): void
    {
        $this->scenario = $this->user->id === $id
            ? User::SCENARIO_UPDATE_SELF
            : ($this->user->isAdmin() ? User::SCENARIO_UPDATE_ADMIN : User::SCENARIO_UPDATE_SUPER);
    }
}
