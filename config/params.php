<?php

declare(strict_types=1);

$params_local = __DIR__ . '/params.local.php';
$params       = [
    'adminEmail'               => '',
    'supportEmail'             => 'nnrudakov@gmail.com',
    'senderName'               => '',
    'cors'                     => [
        'Access-Control-Request-Method'    => ['GET', 'POST', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
        'Access-Control-Request-Headers'   => ['Origin', 'X-Csrf-token', 'Content-type', 'Accept', 'X-Requested-With'],
        'Access-Control-Allow-Credentials' => true,
        'Access-Control-Allow-Methods'     => ['GET', 'POST', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
        'Access-Control-Allow-Headers'     => ['Content-Type', 'X-Csrf-Token', 'X-Requested-With'],
        'Access-Control-Max-Age'           => 86400,
        'Access-Control-Expose-Headers'    => ['X-Csrf-Token', 'X-Requested-With'],
    ],
    'passwordResetTokenExpire' => 86400,
];

return yii\helpers\ArrayHelper::merge($params, file_exists($params_local) ? require $params_local : []);
