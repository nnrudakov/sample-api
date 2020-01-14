<?php

declare(strict_types=1);

namespace tests\functional\users;

use Codeception\Util\HttpCode;
use Page\Users as Url;
use tests\functional\BaseCest;

/**
 * Class PasswordResetCest
 *
 * @package    tests\functional\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class PasswordResetCest extends BaseCest
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

    public function emptyParams(\FunctionalTester $I): void
    {
        $this->url->passwordReset();
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $this->url->passwordReset(['token' => 'token']);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function tokenNotFound(\FunctionalTester $I): void
    {
        $this->url->passwordReset(['token' => 'token', 'password' => 'password']);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
