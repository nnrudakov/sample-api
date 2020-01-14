<?php

declare(strict_types=1);

namespace tests\functional\companies;

use Codeception\Util\HttpCode;
use Page\Companies as Url;
use tests\functional\BaseCest;
use app\models\User;

/**
 * Class CompaniesListCest
 *
 * @package    tests\functional\companies
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class CompaniesListCest extends BaseCest
{
    /**
     * @var $url Url
     */
    private Url $url;

    public function _before(\FunctionalTester $I): void
    {
        parent::_before($I);
        $this->url = new Url($I);
        $I->amLoggedInAs(User::findByEmail('nnrudakov@gmail.com'));
    }

    public function getList(\FunctionalTester $I): void
    {
        $this->url->list();
        $I->seeResponseCodeIs(HttpCode::OK);
    }

    public function getOnlyAvailableList(\FunctionalTester $I): void
    {
        $company1 = $this->createCompany($I);
        $company2 = $this->createCompany($I);
        $user = $this->createUser($I, User::ROLE_ADMIN, $company1);
        $I->amLoggedInAs(User::findByEmail($user->email));
        $this->url->list();
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseJsonMatchesJsonPath("$..[?(@.id={$company1->id})]");
        $I->dontSeeResponseJsonMatchesJsonPath("$..[?(@.id={$company2->id})]");
    }
}
