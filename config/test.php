<?php

declare(strict_types=1);

$config = [
    'id'             => 'sample-api-tests',
    'name'           => 'SAMPLE-API-tests',
    'basePath'       => \dirname(__DIR__),
    'language'       => 'ru',
    'sourceLanguage' => 'en-US',
    'timeZone'       => 'Europe/Moscow',
    'components'     => [
        'session'      => [
            'class' => yii\redis\Session::class,
            'name'  => 'samplesessid',
        ],
        'mailer'       => [
            'useFileTransport' => true,
        ],
        'urlManager'   => require __DIR__ . '/url.php',
        'user'         => [
            'identityClass'   => app\models\User::class,
            'enableAutoLogin' => true,
            'loginUrl'        => null
        ],
        'request'      => [
            'cookieValidationKey'    => 'test',
            'enableCsrfValidation'   => false,
            'enableCookieValidation' => false,
            'parsers'                => [
                'application/json' => yii\web\JsonParser::class,
            ],
        ],
        'response'     => [
            'format'     => yii\web\Response::FORMAT_JSON,
            'formatters' => [
                yii\web\Response::FORMAT_JSON => [
                    'class'       => yii\web\JsonResponseFormatter::class,
                    'prettyPrint' => YII_DEBUG,
                ],
            ],
        ],
        'errorHandler' => [
            'class' => app\components\ErrorHandler::class,
        ],
        'cache'        => [
            'class' => yii\caching\DummyCache::class,
        ],
        'formatter'    => require __DIR__ . '/formatter.php',
        'i18n'         => require __DIR__ . '/i18n.php',
        'authManager'  => [
            'class' => yii\rbac\DbManager::class,
        ],
    ],
    'params'         => require __DIR__ . '/params.php',
];

return yii\helpers\ArrayHelper::merge($config, require __DIR__ . '/test_db.php');
