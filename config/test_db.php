<?php

declare(strict_types=1);

$db = require __DIR__ . '/db.php';
$config = [];

return yii\helpers\ArrayHelper::merge($db, $config);
