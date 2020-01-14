<?php

declare(strict_types=1);

/**
 * Настройки маршрутизации URL.
 */
return [
    'enablePrettyUrl'     => true,
    'enableStrictParsing' => true,
    'showScriptName'      => false,
    'rules'               => [
        'HEAD csrf' => 'site/csrf',
        [
            'class'         => yii\rest\UrlRule::class,
            'controller'    => 'users',
            'pluralize'     => false,
            'except'        => ['delete', 'options'],
            'extraPatterns' => [
                'POST password-reset-request' => 'password-reset-request',
                'POST password-reset'         => 'password-reset',
                'POST login'                  => 'login',
                'POST logout'                 => 'logout',
            ],
        ],
        [
            'class'      => yii\rest\UrlRule::class,
            'controller' => 'companies',
            'pluralize'  => false,
            'except'     => ['delete', 'options'],
        ],
        [
            'class'      => yii\rest\UrlRule::class,
            'controller' => 'access',
            'pluralize'  => false,
            'only'       => ['view', 'update'],
            'tokens'     => [
                '{companyId}' => '<companyId:\\d[\\d,]*>',
                '{userId}' => '<userId:\\d[\\d,]*>',
            ],
            'patterns'   => [
                'PATCH {companyId}/{userId}' => 'update',
                'GET {companyId}/{userId}'   => 'view',
            ],
        ],
    ],
];
