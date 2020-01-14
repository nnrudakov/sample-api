<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\components\behaviors;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\controllers\BaseController;

/**
 * Поведение отправки пароля новому пользователю.
 *
 * После успешного добавления пользователя, на указанную почту отправляется письмо с паролем.
 *
 * @property string $password Пароль пользователя.
 *
 * @package    app\components\behaviors
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class PasswordSendBehavior extends Behavior
{
    /**
     * @var string Пароль пользователя.
     */
    public string $password;

    /**
     * {@inheritdoc}
     */
    public function events(): array
    {
        return [ActiveRecord::EVENT_AFTER_INSERT => 'run'];
    }

    /**
     * Отправка письма.
     */
    public function run(): void
    {
        /** @var \app\models\db\User $user */
        $user = $this->owner;
        /** @var \yii\mail\BaseMailer $mailer */
        $mailer = Yii::$app->mailer;
        try {
            $mailer
                ->compose('password_send', ['name' => $user->name, 'password' => $this->password])
                ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->params['senderName']])
                ->setTo([$user->email => $user->name])
                ->setSubject(Yii::t('app', 'password_send_email_subject', ['app' => Yii::$app->name]))
                ->send();
        } catch (\Exception $e) {
            BaseController::log("User: {$user->email}. PasswordSendBehavior error: {$e->getMessage()}", 'error');
        }
    }
}
