<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\models\search;

use app\models\db\User;

/**
 * Поисковая модель таблицы "users".
 *
 * @package    app\models\search
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class UserSearch extends User
{
    public function rules(): array
    {
        return [
            [['role'], 'in', 'range' => [static::ROLE_ADMIN, static::ROLE_USER], 'strict' => true],
            [['companies'], 'safe']
        ];
    }
}
