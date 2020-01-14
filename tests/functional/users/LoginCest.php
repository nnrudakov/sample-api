<?php

declare(strict_types=1);

namespace tests\functional\users;

use Codeception\Util\HttpCode;
use Page\Users as Url;
use tests\functional\BaseCest;

/**
 * Class LoginCest
 *
 * @package    tests\functional\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class LoginCest extends BaseCest
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
        $this->url->login();
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $this->url->login(['email' => $this->faker->email]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function userNotFound(\FunctionalTester $I): void
    {
        $this->url->login(['email' => $this->faker->email, 'password' => $this->faker->password]);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function invalidPassword(\FunctionalTester $I): void
    {
        $this->url->login(['email' => 'nnrudakov@gmail.com', 'password' => $this->faker->password]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function loginSuccess(\FunctionalTester $I): void
    {
        $this->url->login(['email' => 'nnrudakov@gmail.com', 'password' => 'qv7Gu5mrFQ8X?*4^']);
        $I->seeResponseCodeIs(HttpCode::OK);
        $this->url->login(['email' => 'nnrudakov@gmail.com', 'password' => 'qv7Gu5mrFQ8X?*4^']);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }
}
