<?php

declare(strict_types=1);

$mailer_local = __DIR__ . '/mailer.local.php';
$mailer       = [
    'class'            => yii\swiftmailer\Mailer::class,
    'useFileTransport' => false,
    'transport'        => [
        'class' => \Swift_SmtpTransport::class,
    ],
];

return yii\helpers\ArrayHelper::merge($mailer, file_exists($mailer_local) ? require $mailer_local : []);
