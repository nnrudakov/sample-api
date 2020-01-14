<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

use yii\db\{ColumnSchemaBuilder, Migration};

/**
 * Миграция добавления таблицы пользователей.
 *
 * @package    sample-api
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class m191218_071140_create_users_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('users', [
            'id'            => $this->primaryKey()->comment('Идентификатор'),
            'email'         => $this->string(50)->notNull()->unique()->comment('E-mail'),
            'password_hash' => $this->string()->notNull()->comment('Пароль'),
            'password_reset_token' => $this->string(50)->unique()->comment('Токен восстановления пароля'),
            'name'          => $this->string(50)->notNull()->comment('Имя'),
            'enabled'       => $this->boolean()->defaultValue(false)->comment('Активен'),
            'role'          => $this->string(15)->notNull()->comment('Роль'),
            'companies'     => (new ColumnSchemaBuilder('integer[]'))->null()->comment('Организации'),
            'created_at'    => $this->dateTime()->notNull()->comment('Дата создания'),
            'updated_at'    => $this->dateTime()->notNull()->comment('Дата обновления'),
        ]);
        $this->addCommentOnTable('users', 'Пользователи');
        $this->addForeignKey(
            'fk_user_log',
            'users_logs',
            'user_id',
            'users',
            'id',
            'CASCADE',
            'NO ACTION'
        );
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        $this->insert('users', [
            'email'         => 'nnrudakov@gmail.com',
            'password_hash' => '1',
            'name'          => 'QDev',
            'enabled'       => true,
            'role'          => 'superAdmin',
            'created_at'    => $now,
            'updated_at'    => $now
        ]);
        $auth = Yii::$app->authManager;
        $auth->assign($auth->getRole('superAdmin'), 1);
    }

    public function safeDown(): bool
    {
        $this->dropForeignKey('fk_user_log', 'users_logs');
        $this->dropTable('users');

        return true;
    }
}
