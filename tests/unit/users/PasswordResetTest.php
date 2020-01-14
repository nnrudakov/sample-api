<?php

declare(strict_types=1);

namespace tests\unit\users;

use Codeception\Stub;
use Codeception\Util\HttpCode;
use tests\unit\BaseUnit;
use app\controllers\users\PasswordResetAction;
use app\models\db\User;

/**
 * Class PasswordResetTest
 *
 * @package    tests\unit\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class PasswordResetTest extends BaseUnit
{
    /**
     * @var User
     */
    private User $user;

    protected function _before(): void
    {
        parent::_before();

        $this->user = $this->generateUser();
        $this->user->generatePasswordResetToken();
    }

    public function testPasswordResetFail(): void
    {
        \Yii::$app->request->setBodyParams(['token' => $this->user->password_reset_token, 'password' => '123']);
        /** @var PasswordResetAction $action */
        $action = Stub::make(PasswordResetAction::class, [
            'request' => \Yii::$app->request,
            'response' => \Yii::$app->response,
        ]);
        self::assertInstanceOf(User::class, $action->run());
    }

    public function testPasswordResetSuccess(): void
    {
        \Yii::$app->request->setBodyParams(['token' => $this->user->password_reset_token, 'password' => '12345678']);
        /** @var PasswordResetAction $action */
        $action = Stub::make(PasswordResetAction::class, [
            'request' => \Yii::$app->request,
            'response' => \Yii::$app->response,
        ]);
        self::assertEmpty($action->run());
        self::assertEquals(HttpCode::NO_CONTENT, \Yii::$app->response->statusCode);
    }
}
