<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

use yii\db\Migration;

/**
 * Миграция добавления ролей.
 *
 * @package    sample-api
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class m191216_143835_init_rbac extends Migration
{
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $superAdmin = $auth->createRole('superAdmin');
        $superAdmin->description = 'Главный администратор';
        $auth->add($superAdmin);
        $admin = $auth->createRole('admin');
        $admin->description = 'Администратор';
        $auth->add($admin);
        $auth->addChild($superAdmin, $admin);
        $user = $auth->createRole('user');
        $user->description = 'Пользователь';
        $auth->add($user);
        $auth->addChild($superAdmin, $user);
    }

    public function safeDown(): bool
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();

        return true;
    }
}
