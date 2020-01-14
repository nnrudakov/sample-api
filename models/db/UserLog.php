<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\models\db;

use yii\db\{ActiveRecord, Expression};
use yii\behaviors\TimestampBehavior;

/**
 * Модель таблицы "users_logs".
 *
 * @property integer $id         Идентификатор.
 * @property integer $user_id    Идентификатор пользователя.
 * @property integer $company_id Идентификатор организации.
 * @property string  $action     Действие.
 * @property string  $object_id  Объект.
 * @property string  $ip         IP адрес.
 * @property string  $client     Клиент (браузер или ID устройства).
 * @property string  $created_at Дата создания.
 *
 * @package    app\models\db
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class UserLog extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return 'users_logs';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_at']
                ],
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['user_id', 'action'], 'required'],
            [['company_id'], 'integer'],
            [['ip', 'client', 'created_at'], 'safe'],
            [['action'], 'string', 'max' => 100],
            [['object_id'], 'string', 'max' => 50],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']]
        ];
    }
}
