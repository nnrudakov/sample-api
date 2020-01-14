<?php

declare(strict_types=1);

namespace tests\functional\users;

use Codeception\Stub;
use Codeception\Util\HttpCode;
use Page\Users as Url;
use yii\mail\MessageInterface;
use yii\swiftmailer\Mailer;
use tests\functional\BaseCest;

/**
 * Class PasswordResetRequestCest
 *
 * @package    tests\functional\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class PasswordResetRequestCest extends BaseCest
{
    /**
     * @var $url Url
     */
    private Url $url;

    public function _before(\FunctionalTester $I): void
    {
        parent::_before($I);
        $this->url = new Url($I);
    }

    public function passwordResetRequestEmptyOrWrongEmail(\FunctionalTester $I): void
    {
        $this->url->passwordResetRequest();
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function sendPasswordResetRequestSuccess(\FunctionalTester $I): void
    {
        $user = $this->createUserModel($I);
        $this->url->passwordResetRequest(['email' => $user->email]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $emailMessage = $I->grabLastSentEmail();
        expect('valid email is sent', $emailMessage)->isInstanceOf(MessageInterface::class);
        expect($emailMessage->getTo())->hasKey($user->email);
    }

    public function sendPasswordResetRequestFail(\FunctionalTester $I): void
    {
        $user = $this->createUserModel($I);
        \Yii::$app->set('mailer', Stub::make(Mailer::class, ['compose' => static function () {
            throw new \ErrorException('error');
        }]));
        $this->url->passwordResetRequest(['email' => $user->email]);
        $I->seeResponseCodeIs(HttpCode::INTERNAL_SERVER_ERROR);
    }
}
