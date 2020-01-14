<?php

declare(strict_types=1);

namespace tests\unit\users;

use Codeception\Stub;
use tests\unit\BaseUnit;
use yii\rest\Controller;
use app\controllers\exceptions\ServerException;
use app\controllers\users\LogoutAction;
use app\models\db\User;

/**
 * Class LogoutTest
 *
 * @package    tests\unit\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class LogoutTest extends BaseUnit
{
    public function testLoginFail(): void
    {
        \Yii::$app->set('user', Stub::make(\yii\web\User::class, ['logout' => static function () { return false; }]));
        /** @var LogoutAction $action */
        $action = \Yii::createObject(LogoutAction::class, [
            'logout',
            Stub::make(Controller::class),
            ['modelClass' => User::class, 'webUser' => \Yii::$app->user]
        ]);
        $this->expectException(ServerException::class);
        $action->run();
    }
}
