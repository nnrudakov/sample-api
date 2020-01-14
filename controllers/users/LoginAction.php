<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\controllers\users;

use yii\web\User as WebUser;
use app\controllers\BaseAction;
use app\controllers\exceptions\{InvalidParamException, NotFoundException, ServerException};
use app\components\ErrorHandler;
use app\models\User;

/**
 * Действие входа в систему.
 *
 * Получает адрес электронной почты, пароль и выполняет вход в систему.
 *
 * @property \yii\web\User $webUser Системный компонент пользователя.
 *
 * @package    app\controllers\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class LoginAction extends BaseAction
{
    /**
     * @var WebUser Системный компонент пользователя.
     */
    public WebUser $webUser;

    /**
     * @var User Пользователь.
     */
    private ?User $user = null;
    /**
     * @var string E-mail пользователя.
     */
    private ?string $email = null;
    /**
     * @var string Пароль.
     */
    private ?string $password = null;

    /**
     * @OA\Post(
     *     path="/users/login",
     *     operationId="users-login",
     *     tags={"Пользователи"},
     *     summary="Вход в систему",
     *     description="Получает адрес электронной почты, пароль и выполняет вход в систему.",
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
     *         description="Пользователь",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"email", "password"},
     *                 @OA\Property(
     *                      property="email",
     *                      title="E-mail",
     *                      description="Адрес электронной почты, под которым зарегистрирован пользователь",
     *                      type="string"
     *                 ),
     *                 @OA\Property(
     *                      property="password",
     *                      title="Пароль",
     *                      description="Разрешается использовать только буквы английского алфавита, цифры и спец. символы. Минимум 6 символов.",
     *                      type="string",
     *                      format="password"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Успешный вход в систему",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                      property="id",
     *                      title="Идентификатор пользователя",
     *                      type="integer",
     *                      format="int64"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Неверный CSRF токен или неверные параметры запроса (не указан адрес, пароль или пароль неверный)",
     *          @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *          response="403",
     *          description="Повторный вызов метода если пользователь уже авторизован",
     *          @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Пользователь не найден или заблокирован",
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
     * @return array
     *
     * @throws InvalidParamException Если E-mail или пароль не указаны, или пароль неверный
     * @throws NotFoundException Если пользователь не найден или заблокирован
     * @throws ServerException Если войти не удалось
     */
    public function run(): array
    {
        $this->validate();
        $this->findUser();
        $this->login();

        return ['id' => $this->user->id];
    }

    /**
     * Валидация параметров запроса.
     *
     * @throws InvalidParamException Если E-mail или пароль не указаны
     */
    private function validate(): void
    {
        $this->email = $this->request->getBodyParam('email');
        if (!$this->email || !\filter_var($this->email, \FILTER_VALIDATE_EMAIL)) {
            throw new InvalidParamException('login_no_email');
        }
        if (!$this->password = $this->request->getBodyParam('password')) {
            throw new InvalidParamException('login_no_password');
        }
    }

    /**
     * Поиск пользователя по переданным данным.
     *
     * @throws NotFoundException Если пользователь не найден
     */
    private function findUser(): void
    {
        if (!$this->user = User::findByEmail($this->email)) {
            throw new NotFoundException('user_not_found');
        }
    }

    /**
     * Установка нового пароля.
     *
     * @throws InvalidParamException Если пароль неверный
     * @throws ServerException Если войти не удалось
     */
    private function login(): void
    {
        if (!$this->user->validatePassword($this->password)) {
            throw new InvalidParamException('login_invalid_password');
        }

        if (!$this->webUser->login($this->user, 2592000)) { // a month
            throw new ServerException(500, 'login_fail', ErrorHandler::INVALID_LOGIN);
        }
    }
}
