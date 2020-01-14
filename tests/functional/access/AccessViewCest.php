<?php

declare(strict_types=1);

namespace tests\functional\access;

use Codeception\Util\HttpCode;
use Page\Access as Url;
use tests\functional\BaseCest;
use app\models\User;

/**
 * Class AccessViewCest
 *
 * @package    tests\functional\access
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class AccessViewCest extends BaseCest
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

    public function getAccessOfSuperAdmin(\FunctionalTester $I): void
    {
        $this->url->view(1, User::SUPER_ID);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    public function failGetAccessOfAdminByAdmin(\FunctionalTester $I): void
    {
        $admin = $this->createUser($I);
        $I->amLoggedInAs(User::findByEmail($admin->email));
        $this->url->view(1, User::SUPER_ID);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);

        $this->url->view(1, $admin->id);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    public function failGetAccessOfUserByAdmin(\FunctionalTester $I): void
    {
        $admin = $this->createUser($I);
        $user = $this->createUser($I, User::ROLE_USER);
        $I->amLoggedInAs(User::findByEmail($admin->email));

        $this->url->view(1, $user->id);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    public function getAccessOfAdmin(\FunctionalTester $I): void
    {
        $company = $this->createCompany($I);
        $admin = $this->createUser($I, User::ROLE_ADMIN, $company);
        $this->url->view($company->id, $admin->id);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['manageUsers' => false]);
    }

    public function getAccessOfUserByAdmin(\FunctionalTester $I): void
    {
        $company = $this->createCompany($I);
        $admin = $this->createUser($I, User::ROLE_ADMIN, $company, ['manageUsers' => true]);
        $user = $this->createUser($I, User::ROLE_USER, $company);
        $I->amLoggedInAs(User::findByEmail($admin->email));

        $this->url->view($company->id, $user->id);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->dontSeeResponseContainsJson(['manageUsers' => true]);
        $I->dontSeeResponseContainsJson(['manageUsers' => false]);
    }
}
