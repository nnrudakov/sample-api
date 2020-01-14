<?php

declare(strict_types=1);

namespace tests\functional;

use Codeception\Util\HttpCode;
use Page\Site as Url;

/**
 * Class SiteCest
 *
 * @package    tests\functional
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class SiteCest extends BaseCest
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

    public function getCsrfSuccess(\FunctionalTester $I): void
    {
        $this->url->csrf();
        $I->seeHttpHeader('X-Csrf-Token');
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }

    public function getCsrfFail(\FunctionalTester $I): void
    {
        $this->url->csrf('GET');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->cantSeeHttpHeader('X-Csrf-Token');
    }
}
