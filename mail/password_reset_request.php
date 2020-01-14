<?php
declare(strict_types=1);

use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $message \yii\mail\MessageInterface */
/* @var $user \app\models\db\User */
/* @var $tillDate \DateTime */

$this->title = 'Восстановление пароля';
$reset_link = str_replace('api.', '', Yii::$app->request->hostInfo) . '/password-reset?token=' . $user->password_reset_token;
?>
<div>
    <p>Здравствуйте, <?= Html::encode($user->name) ?>.</p>
    <p>Для восстановления пароля перейдите по ссылке:</p>
    <p><?= Html::a(Html::encode($reset_link), $reset_link) ?></p>
    <p>Ссылка действует <strong>до <?= Yii::$app->formatter->asDatetime($tillDate, 'long') ?></strong>.</p>
</div>
