<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\components\validators;

use libphonenumber\{NumberParseException, PhoneNumberUtil};
use Yii;
use yii\validators\Validator;
use app\controllers\BaseController;

/**
 * Валидация номера телефона.
 *
 * @package    app\components\validators
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class PhoneValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->message = Yii::t('yii', 'The format of {attribute} is invalid.');

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value): ?array
    {
        $valid = false;
        $phoneUtil = PhoneNumberUtil::getInstance();
        try {
            $phoneProto = $phoneUtil->parse($value, 'RU');
            if ($phoneUtil->isValidNumber($phoneProto)) {
                $valid = true;
            }
        } catch (NumberParseException $e) {
            BaseController::log(__CLASS__ . " error: {$e->getMessage()}");
        }

        return $valid ? null : [$this->message, []];
    }
}
