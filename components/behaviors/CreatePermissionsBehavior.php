<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\components\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use app\models\Access;

/**
 * Поведение создания разрешений организации.
 *
 * Поведение срабатывает после успешного создания организации. Для каждой организации создаётся свой собственный список
 * разрешений. К имени разрешения добавляется идентификатор организации. Конечному пользователю назначается и роль, и
 * разрешение. проверка разрешений осуществляется стандартным механизмом `RBAC` с указанием имени разрешения и
 * идентификатора организации.
 *
 * @package    app\components\behaviors
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 *
 * @see https://www.yiiframework.com/doc/guide/2.0/en/security-authorization#rbac
 */
class CreatePermissionsBehavior extends Behavior
{
    /**
     * {@inheritdoc}
     */
    public function events(): array
    {
        return [ActiveRecord::EVENT_AFTER_INSERT => 'run'];
    }

    /**
     * Создание разрешений.
     */
    public function run(): void
    {
        /** @var \app\models\db\Company $this->owner */
        (new Access(['companyId' => $this->owner->id]))->createCompanyPermissions();
    }
}
