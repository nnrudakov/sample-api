<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\controllers\exceptions;

use Yii;
use yii\web\NotFoundHttpException;
use app\components\ErrorHandler;

/**
 * Исключение не найденной сущности (страницы, объекта и т.п.).
 *
 * @package    app\controllers\exceptions
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class NotFoundException extends NotFoundHttpException
{
    /**
     * {@inheritdoc}
     * @noinspection PhpUnusedParameterInspection
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(Yii::t('app/errors', $message), ErrorHandler::NOT_FOUND, $previous);
    }
}
