<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\controllers\users;

use Yii;
use app\controllers\{BaseController, BaseAction};
use app\controllers\exceptions\{NotFoundException, ServerException};
use app\models\db\User;

/**
 * Действие запроса восстановления пароля.
 *
 * Получает адрес зарегистрированного пользователя, генерирует уникальный код восстановления пароля и отправляет его
 * пользователю.
 *
 * @package    app\controllers\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class PasswordResetRequestAction extends BaseAction
{
    /**
     * @var User Пользователь.
     */
    private User $user;

    /**
     * @OA\Post(
     *     path="/users/password-reset-request",
     *     operationId="users-password-reset-request",
     *     tags={"Пользователи"},
     *     summary="Запрос восстановления пароля",
     *     description="Получает адрес зарегистрированного пользователя, генерирует уникальный код восстановления пароля и отправляет его пользователю. Ссылка действует 24 часа.<br>После перехода по ссылке пользователь должен ввести новый пароль, затем вызван метод [/users/password-reset](#operation/users-password-reset).",
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
     *         description="Адрес пользователя",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"email"},
     *                 @OA\Property(
     *                     property="email",
     *                     title="Адрес электронной почты",
     *                     type="string"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *          response="204",
     *          description="Письмо успешно отправлено"
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Неверный CSRF токен",
     *          @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Адрес не передан, пользователь с переданным адресом не найден либо заблокирован",
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
     * @throws NotFoundException Если пользователь не найден или заблокирован
     * @throws ServerException Если не удалось отправить письмо
     */
    public function run(): void
    {
        $this->findUser();
        $this->generateResetToken();
        $this->sendEmail();
        $this->response->setStatusCode(204, 'Password Reset E-mail Sent');
    }

    /**
     * Поиск пользователя по переданной электронной почте.
     *
     * @throws NotFoundException Если пользователь не найден
     */
    private function findUser(): void
    {
        $email = $this->request->getBodyParam('email');
        if (!\filter_var($email, \FILTER_VALIDATE_EMAIL) || !$this->user = User::findByEmail($email)) {
            throw new NotFoundException('user_not_found');
        }
        $this->logUserId = $this->user->id;
    }

    /**
     * Генерация токена для восстановления пароля.
     */
    private function generateResetToken(): void
    {
        if (!User::isPasswordResetTokenValid($this->user->password_reset_token)) {
            $this->user->generatePasswordResetToken();
        }
    }

    /**
     * Отправка письма со ссылкой для восстановления пароля.
     *
     * @throws ServerException Если не удалось отправить письмо
     */
    private function sendEmail(): void
    {
        /** @var yii\mail\BaseMailer $mailer */
        $mailer = Yii::$app->mailer;
        $tillDate = new \DateTime();
        $tillDate->setTimestamp(time() + Yii::$app->params['passwordResetTokenExpire']);

        try {
            $mailer
                ->compose('password_reset_request', ['user' => $this->user, 'tillDate' => $tillDate])
                ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->params['senderName']])
                ->setTo([$this->user->email => $this->user->name])
                ->setSubject(Yii::t('app', 'password_reset_email_subject', ['app' => Yii::$app->name]))
                ->send();
        } catch (\Exception $e) {
            BaseController::log("User ID: {$this->user->id}. {$this->id} error: {$e->getMessage()}", 'error');
            throw new ServerException(500, 'password_reset_request_fail', $e->getCode(), $e);
        }
    }
}
