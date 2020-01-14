<?php

declare(strict_types=1);

return [
    'defaultTimeZone'            => 'Europe/Moscow',
    'timeZone'                   => 'Europe/Moscow',
    'dateFormat'                 => 'dd.MM.yyyy',
    'datetimeFormat'             => 'dd.MM.yyyy HH:mm',
    'currencyCode'               => 'RUR',
    'numberFormatterSymbols'     => [
        NumberFormatter::CURRENCY_SYMBOL => '₽',
    ],
    'numberFormatterTextOptions' => [
        NumberFormatter::NEGATIVE_PREFIX => '&minus;',
    ],
    'numberFormatterOptions'     => [
        NumberFormatter::FRACTION_DIGITS => 0
    ]
];
