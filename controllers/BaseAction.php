<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\controllers;

/**
 * Базовое действие, которое должны наследовать все действия.
 *
 * @package    app\controllers
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
abstract class BaseAction extends \yii\rest\Action
{
    use ActionTrait;
}
