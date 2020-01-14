<?php

declare(strict_types=1);

namespace tests\functional\users;

use Codeception\Util\HttpCode;
use Page\Users as Url;
use tests\functional\BaseCest;

/**
 * Class LogoutCest
 *
 * @package    tests\functional\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class LogoutCest extends BaseCest
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

    public function logoutSuccess(\FunctionalTester $I): void
    {
        $this->url->login(['email' => 'nnrudakov@gmail.com', 'password' => 'qv7Gu5mrFQ8X?*4^']);
        $I->seeResponseCodeIs(HttpCode::OK);
        $this->url->logout();
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }
}
