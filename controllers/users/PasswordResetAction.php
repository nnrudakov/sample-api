<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\controllers\users;

use app\controllers\BaseAction;
use app\controllers\exceptions\{InvalidParamException, NotFoundException};
use app\models\db\User;

/**
 * Действие установки нового пароля.
 *
 * Получает новый пароль введённый в форме.
 *
 * @package    app\controllers\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class PasswordResetAction extends BaseAction
{
    /**
     * @var User Пользователь.
     */
    private ?User $user = null;
    /**
     * @var string Токен восстановления пароля.
     */
    private ?string $token = null;
    /**
     * @var string Новый пароль.
     */
    private ?string $password = null;

    /**
     * @OA\Post(
     *     path="/users/password-reset",
     *     operationId="users-password-reset",
     *     tags={"Пользователи"},
     *     summary="Установка нового пароля",
     *     description="Получает токен восстановления и новый пароль введённый в форме и записывает пользователю, которому соответствует токен.",
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
     *         description="Новый пароль",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"token", "password"},
     *                 @OA\Property(
     *                      property="token",
     *                      title="Токен восстановления пароля",
     *                      description="Токен должен быть получен из ссылки письма, которое было отправлено пользователю после вызова метода [/users/password-reset-request](#operation/users-password-reset-request).",
     *                      type="string"
     *                 ),
     *                 @OA\Property(
     *                      property="password",
     *                      title="Новый пароль",
     *                      description="Разрешается использовать только буквы английского алфавита, цифры и спец. символы. Минимум 6 символов.",
     *                      type="string",
     *                      format="password"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *          response="204",
     *          description="Пароль успешно изменён"
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Неверный CSRF токен или неверные параметры запроса (не указан токен восстановления пароля или новый пароль)",
     *          @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Пользователь не найден или время жизни токена восстановления истекло",
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
     * Запуск действия.
     *
     * @return User|null
     *
     * @throws InvalidParamException Если токен или пароль не указаны
     * @throws NotFoundException Если пользователь не найден или заблокирован
     */
    public function run(): ?User
    {
        $this->validate();
        $this->findUser();
        if ($this->resetPassword()) {
            $this->response->setStatusCode(204, 'Password Reset Done');

            return null;
        }

        return $this->user;
    }

    /**
     * Валидация параметров запроса.
     *
     * @throws InvalidParamException Если токен или пароль не указаны
     */
    private function validate(): void
    {
        if (!$this->token = $this->request->getBodyParam('token')) {
            throw new InvalidParamException('password_reset_no_token');
        }
        if (!$this->password = $this->request->getBodyParam('password')) {
            throw new InvalidParamException('password_reset_no_password');
        }
    }

    /**
     * Поиск пользователя по переданному токену.
     *
     * @throws NotFoundException Если пользователь не найден
     */
    private function findUser(): void
    {
        if (!$this->user = User::findByPasswordResetToken($this->token)) {
            throw new NotFoundException('password_reset_invalid_token');
        }
        $this->logUserId = $this->user->id;
    }

    /**
     * Установка нового пароля.
     *
     * @return bool
     */
    private function resetPassword(): bool
    {
        $this->user->setScenario(User::SCENARIO_PASSWORD_RESET);
        $this->user->password = $this->password;
        $this->user->password_reset_token = null;

        return $this->user->save();
    }
}
