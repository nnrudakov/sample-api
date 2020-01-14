<?php

declare(strict_types=1);

namespace tests\unit\companies;

use Codeception\Stub;
use tests\unit\BaseUnit;
use app\components\behaviors\CreatePermissionsBehavior;
use app\models\db\{Company, User};

/**
 * Class CreatePermissionsBehaviorTest
 *
 * @package    tests\unit\companies
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class CreatePermissionsBehaviorTest extends BaseUnit
{
    public function testCreatePermissions(): void
    {
        $behavior = \Yii::createObject([
            'class' => CreatePermissionsBehavior::class,
            'owner' => Stub::make(Company::class, ['id' => 1])
        ]);
        $behavior->run();
        $sql = 'SELECT name FROM auth_item WHERE name=:name';
        expect_that(User::getDb()->createCommand($sql, [':name' => 'manageUsers1'])->queryScalar());
    }
}
