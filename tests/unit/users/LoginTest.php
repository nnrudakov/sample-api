<?php

declare(strict_types=1);

namespace tests\unit\users;

use Codeception\Stub;
use tests\unit\BaseUnit;
use yii\rest\Controller;
use app\controllers\exceptions\ServerException;
use app\controllers\users\LoginAction;
use app\models\db\User;

/**
 * Class LoginTest
 *
 * @package    tests\unit\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class LoginTest extends BaseUnit
{
    public function testLoginFail(): void
    {
        \Yii::$app->request->setBodyParams(['email' => 'nnrudakov@gmail.com', 'password' => 'qv7Gu5mrFQ8X?*4^']);
        \Yii::$app->set('user', Stub::make(\yii\web\User::class, ['login' => static function () { return false; }]));
        /** @var LoginAction $action */
        $action = \Yii::createObject(LoginAction::class, [
            'login',
            Stub::make(Controller::class),
            ['modelClass' => User::class, 'webUser' => \Yii::$app->user, 'request' => \Yii::$app->request]
        ]);
        $this->expectException(ServerException::class);
        $action->run();
    }
}
