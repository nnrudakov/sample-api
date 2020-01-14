<?php

declare(strict_types=1);

return [
    'translations' => [
        'app*' => [
            'class'   => yii\i18n\PhpMessageSource::class,
            'fileMap' => [
                'app'        => 'app.php',
                'app/errors' => 'errors.php',
            ],
        ],
    ],
];
