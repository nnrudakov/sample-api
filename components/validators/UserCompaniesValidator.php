<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\components\validators;

use Yii;
use yii\validators\Validator;
use app\models\db\Company;

/**
 * Валидация привязки пользователя к организации.
 *
 * @package    app\components\validators
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class UserCompaniesValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->message = Yii::t('app/errors', 'users_invalid_company', ['id' => '{id}']);

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value): ?array
    {
        $valid = true;
        $id = 0;
        foreach ($value as $id) {
            /** @noinspection UnnecessaryCastingInspection */
            if (!$this->isCompanyValid((int) $id)) {
                $valid = false;
                break;
            }
        }

        return $valid ? null : [$this->message, ['id' => $id]];
    }

    /**
     * @param integer $id Идентификатор организации.
     *
     * @return bool
     */
    private function isCompanyValid(int $id): bool
    {
        if (!Company::find()->select(['id'])->where(['id' => $id])->exists()) {
            return false;
        }

        return true;
    }
}
