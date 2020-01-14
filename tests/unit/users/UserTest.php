<?php

declare(strict_types=1);

namespace tests\unit\users;

use tests\unit\BaseUnit;
use app\models\User;

/**
 * Class UserTest
 *
 * @package    tests\unit\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class UserTest extends BaseUnit
{
    public function testFindByEmail(): void
    {
        $company = $this->generateCompany(false);
        $user = $this->generateUser(User::ROLE_USER, $company);
        expect_not(User::findByEmail($user->email));
    }
}
