<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\models;

use yii\base\NotSupportedException;
use yii\web\IdentityInterface;
use app\models\db\User as BaseUser;

/**
 * Модель пользователя системы.
 *
 * @package    app\models
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class User extends BaseUser implements IdentityInterface
{
    /**
     * {@inheritdoc}
     *
     * @return User|null
     */
    public static function findIdentity($id): ?self
    {
        return static::find()->select(['id', 'name'])->where(['id' => $id, 'enabled' => true])->one();
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey): bool
    {
        return true;
    }
}
