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
 * Поведение привязки роли пользователя в ролевой моделе RBAC.
 *
 * Поведение срабатывает после успешного создания пользователя.
 *
 * @package    app\components\behaviors
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 *
 * @see https://www.yiiframework.com/doc/guide/2.0/en/security-authorization#rbac
 */
class AssignRoleBehavior extends Behavior
{
    /**
     * {@inheritdoc}
     */
    public function events(): array
    {
        return [ActiveRecord::EVENT_AFTER_INSERT => 'run'];
    }

    /**
     * Привязка роли.
     */
    public function run(): void
    {
        /** @var \app\models\db\User $this->owner */
        (new Access(['userId' => $this->owner->id]))->assignRole($this->owner->role);
    }
}
