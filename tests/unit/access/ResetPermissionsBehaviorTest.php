<?php

declare(strict_types=1);

namespace tests\unit\access;

use Codeception\Stub;
use tests\unit\BaseUnit;
use yii\db\ArrayExpression;
use app\components\behaviors\ResetPermissionsBehavior;
use app\models\db\User;

/**
 * Class ResetPermissionsBehaviorTest
 *
 * @package    tests\unit\access
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class ResetPermissionsBehaviorTest extends BaseUnit
{
    public function testChangeRole(): void
    {
        $behavior = \Yii::createObject([
            'class' => ResetPermissionsBehavior::class,
            'owner' => Stub::make(User::class, ['id' => 1, 'companies' => [1 ,2]]),
            'roleChanged' => true,
            'oldCompanies' => null
        ]);
        $behavior->run();
    }

    public function testChangeCompanies(): void
    {
        $behavior = \Yii::createObject([
            'class' => ResetPermissionsBehavior::class,
            'owner' => Stub::make(User::class, ['id' => 1, 'companies' => [1 ,2]]),
            'roleChanged' => false,
            'oldCompanies' => new ArrayExpression([2, '3'])
        ]);
        $behavior->run();
    }
}
