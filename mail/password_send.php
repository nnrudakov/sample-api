<?php
declare(strict_types=1);

use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $message \yii\mail\MessageInterface */
/* @var $name string */
/* @var $password string */

$this->title = 'Регистрация';
$site_link = str_replace('api.', '', Yii::$app->request->hostInfo);
$login_link = $site_link . '/login';
?>
<div>
    <p>Здравствуйте, <?= Html::encode($name) ?>.</p>
    <p>Вас зарегистрировали на сайте <?= Html::a(Html::encode($site_link), $site_link) ?>.</p>
    <p>Ваш пароль <b><?= Html::encode($password) ?></b>.</p>
    <p>Вы можете войти на сайт по ссылке: <?= Html::a(Html::encode($login_link), $login_link) ?>.</p>
    <p><b>Рекомендуем сменить пароль после входа.</b></p>
</div>
