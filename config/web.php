<?php

declare(strict_types=1);

$web_local = __DIR__ . '/web.local.php';
$config    = [
    'id'             => 'sample-api',
    'name'           => 'SAMPLE-API',
    'version'        => '0.1.0',
    'basePath'       => \dirname(__DIR__),
    'bootstrap'      => ['log'],
    'language'       => 'ru',
    'sourceLanguage' => 'en-US',
    'timeZone'       => 'Europe/Moscow',
    'components'     => [
        'request'      => [
            'cookieValidationKey' => 'oILeKQw3htlr1binN2Pmwjfa7Jy6xwrW',
            'parsers'             => [
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
        'cache'        => [
            'class'           => yii\redis\Cache::class,
            'keyPrefix'       => 'SAMPLE',
            'defaultDuration' => 10,
        ],
        'session'      => [
            'class' => yii\redis\Session::class,
            'name'  => 'samplesessid',
        ],
        'user'         => [
            'identityClass'   => app\models\User::class,
            'enableAutoLogin' => true,
            'loginUrl'        => null
        ],
        'errorHandler' => [
            'class' => app\components\ErrorHandler::class,
        ],
        'mailer'       => require __DIR__ . '/mailer.php',
        'log'          => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                'app'  => [
                    'class'   => yii\log\FileTarget::class,
                    'levels'  => ['error', 'warning'],
                    'logVars' => null
                ],
                'sample' => [
                    'class'          => yii\log\FileTarget::class,
                    'levels'         => ['error', 'warning', 'info'],
                    'categories'     => ['sample'],
                    'logFile'        => '@runtime/logs/sample.log',
                    'logVars'        => null,
                    'exportInterval' => 1,
                ],
            ],
        ],
        'urlManager'   => require __DIR__ . '/url.php',
        'view'         => require __DIR__ . '/view.php',
        'formatter'    => require __DIR__ . '/formatter.php',
        'i18n'         => require __DIR__ . '/i18n.php',
        'authManager'  => [
            'class' => yii\rbac\DbManager::class,
            'cache' => 'cache'
        ],
    ],
    'params'         => require __DIR__ . '/params.php',
];

return yii\helpers\ArrayHelper::merge(
    $config,
    require __DIR__ . '/db.php',
    file_exists($web_local) ? require $web_local : []
);
