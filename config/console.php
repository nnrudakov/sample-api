<?php

declare(strict_types=1);

$console_local = __DIR__ . '/console.local.php';
$config        = [
    'id'                  => 'sample-api-console',
    'name'                => 'SAMPLE-API-console',
    'basePath'            => \dirname(__DIR__),
    'bootstrap'           => ['log'],
    'language'            => 'ru',
    'sourceLanguage'      => 'en-US',
    'timeZone'            => 'Europe/Moscow',
    'controllerNamespace' => 'app\commands',
    'components'          => [
        'cache'       => [
            'class'     => yii\redis\Cache::class,
            'keyPrefix' => 'SAMPLE',
        ],
        'log'         => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                'app' => [
                    'class'   => yii\log\FileTarget::class,
                    'levels'  => ['error', 'warning'],
                    'logVars' => null
                ],
            ],
        ],
        'authManager' => [
            'class' => yii\rbac\DbManager::class,
        ],
        'formatter'   => require __DIR__ . '/formatter.php',
        'i18n'        => require __DIR__ . '/i18n.php',
    ],
    'params'              => require __DIR__ . '/params.php',
    'controllerMap'       => [
        'migrate' => [
            'class'                  => yii\console\controllers\MigrateController::class,
            'templateFile'           => '@app/views/migrations/migration.php',
            'generatorTemplateFiles' => [
                'create_table' => '@app/views/migrations/createTableMigration.php',
            ],
            'migrationPath'          => ['@app/migrations', '@yii/rbac/migrations'],
        ],
    ],
];

$config = yii\helpers\ArrayHelper::merge($config, require __DIR__ . '/db.php');

return yii\helpers\ArrayHelper::merge($config, file_exists($console_local) ? require $console_local : []);
