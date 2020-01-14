<?php

declare(strict_types=1);

namespace tests\unit\users;

use tests\unit\BaseUnit;
use app\models\User;

/**
 * Class WebUserTest
 *
 * @package    tests\unit\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class WebUserTest extends BaseUnit
{
    public function testFindUserById(): void
    {
        expect_that($user = User::findIdentity(1));
        expect($user->name)->equals('QDev');

        expect_not(User::findIdentity(0));
    }

    public function testFindUserByAccessToken(): void
    {
        $this->expectException(\yii\base\NotSupportedException::class);
        User::findIdentityByAccessToken('100-token');
    }

    public function testGetAuthKey(): void
    {
        expect_not((new User)->getAuthKey());
    }

    public function testValidateAuthKey(): void
    {
        expect_that((new User)->validateAuthKey('test100key'));
    }
}
