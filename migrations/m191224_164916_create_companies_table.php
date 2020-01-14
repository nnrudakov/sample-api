<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

use yii\db\Migration;

/**
 * Миграция добавления таблицы организаций.
 *
 * @package    sample-api
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class m191224_164916_create_companies_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('companies', [
            'id'             => $this->primaryKey()->comment('Идентификатор'),
            'title'          => $this->string(100)->notNull()->comment('Наименование'),
            'inn'            => $this->char(10)->notNull()->unique()->comment('ИНН'),
            'ogrn'           => $this->char(13)->notNull()->unique()->comment('ОГРН'),
            'address'        => $this->string(256)->notNull()->comment('Адрес'),
            'phone'          => $this->string(20)->notNull()->comment('Телефон'),
            'person'         => $this->string(100)->notNull()->comment('Ответственное лицо'),
            'contact'        => $this->string(256)->notNull()->comment('Контакт ответственного лица'),
            'enabled'        => $this->boolean()->defaultValue(false)->comment('Активна'),
            'comment'        => $this->string(512)->comment('Комментарий'),
            'scada_host'     => $this->string(50)->notNull()->comment('Хост БД SCADA'),
            'scada_port'     => $this->integer(5)->notNull()->comment('Порт БД SCADA'),
            'scada_db'       => $this->string(50)->notNull()->comment('Имя БД SCADA'),
            'scada_user'     => $this->string(50)->notNull()->comment('Пользователь БД SCADA'),
            'scada_password' => $this->string(50)->notNull()->comment('Пароль БД SCADA'),
            'created_at'     => $this->dateTime()->notNull()->comment('Дата создания'),
            'updated_at'     => $this->dateTime()->notNull()->comment('Дата обновления'),
        ]);
        $this->addCommentOnTable('companies', 'Организации');
        $this->addForeignKey(
            'fk_company_log',
            'users_logs',
            'company_id',
            'companies',
            'id',
            'CASCADE',
            'NO ACTION'
        );
    }

    public function safeDown(): bool
    {
        $this->dropForeignKey('fk_company_log', 'users_logs');
        $this->dropTable('companies');

        return true;
    }
}
