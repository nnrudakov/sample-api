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

/**
 * Действие выхода из систему.
 *
 * Выполняет выход пользователя из системы.
 *
 * @property WebUser $webUser Системный компонент пользователя.
 *
 * @package    app\controllers\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class LogoutAction extends BaseAction
{
    /**
     * @var WebUser Системный компонент пользователя.
     */
    public WebUser $webUser;

    /**
     * @OA\Post(
     *     path="/users/logout",
     *     operationId="users-logout",
     *     tags={"Пользователи"},
     *     summary="Выход из системы",
     *     description="Выполняет выход пользователя из системы.",
     *     @OA\Parameter(
     *         name="X-Csrf-Token",
     *         in="header",
     *         description="CSRF токен",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *          response="204",
     *          description="Успешный выход из системы"
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Неверный CSRF токен",
     *          @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *          response="403",
     *          description="Повторный вызов метода если пользователь уже вышел из системы",
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
     * @throws InvalidParamException Если E-mail или пароль не указаны, или пароль неверный
     * @throws NotFoundException Если пользователь не найден или заблокирован
     * @throws ServerException Если войти не удалось
     */
    public function run(): void
    {
        if (!$this->webUser->logout()) {
            throw new ServerException(500, 'logout_fail', ErrorHandler::INVALID_LOGIN);
        }

        $this->response->setStatusCode(204, 'Logout Done');
    }
}
