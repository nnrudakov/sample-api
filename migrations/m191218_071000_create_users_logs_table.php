<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

use yii\db\Migration;

/**
 * Миграция создания таблицы логов.
 *
 * @package    sample-api
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class m191218_071000_create_users_logs_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('users_logs', [
            'id'         => $this->primaryKey()->comment('Идентификатор'),
            'user_id'    => $this->integer()->notNull()->comment('Идентификатор пользователя'),
            'company_id' => $this->integer()->comment('Идентификатор организации'),
            'action'     => $this->string(100)->notNull()->comment('Действие'),
            'object_id'  => $this->string(50)->comment('Объект'),
            'ip'         => $this->string(50)->comment('IP адрес'),
            'client'     => $this->string(256)->comment('Клиент'),
            'created_at' => $this->dateTime()->notNull()->comment('Дата создания'),
        ]);
        $this->addCommentOnTable('users_logs', 'Журнал действий пользователей');
    }

    public function safeDown(): bool
    {
        $this->dropTable('users_logs');

        return true;
    }
}
