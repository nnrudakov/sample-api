<?php

declare(strict_types=1);

$db_local = __DIR__ . '/db.local.php';

$config = [
    'components' => [
        'db'    => [
            'class'        => yii\db\Connection::class,
            'dsn'          => '',
            'charset'      => 'utf8',
            'queryBuilder' => [
                'expressionBuilders' => [
                    yii\db\conditions\InCondition::class => app\components\db\InConditionBuilder::class,
                ],
            ],
        ],
        'redis' => [
            'class' => yii\redis\Connection::class,
        ],
    ]
];

return yii\helpers\ArrayHelper::merge($config, file_exists($db_local) ? require $db_local : []);
