<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\controllers\exceptions;

use Yii;
use yii\web\HttpException;

/**
 * Исключение неопределённых ошибок.
 *
 * @package    app\controllers\exceptions
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class ServerException extends HttpException
{
    /**
     * {@inheritdoc}
     */
    public function __construct($status, $message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct($status, Yii::t('app/errors', $message), $code, $previous);
    }
}
