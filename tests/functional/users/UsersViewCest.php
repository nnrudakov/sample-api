<?php

declare(strict_types=1);

namespace tests\functional\users;

use Codeception\Util\HttpCode;
use Page\Users as Url;
use tests\functional\BaseCest;
use app\models\User;

/**
 * Class UsersViewCest
 *
 * @package    tests\functional\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class UsersViewCest extends BaseCest
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

    public function getUser(\FunctionalTester $I): void
    {
        $this->url->view(User::SUPER_ID);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['id' => 1]);
        $I->dontSeeResponseContainsJson(['role' => 'superAdmin']);

        $this->url->view(User::SUPER_ID, ['expand' => 'role']);
        $I->seeResponseContainsJson(['role' => 'superAdmin']);
    }

    public function failViewSuperAdminByNotSuperAdmin(\FunctionalTester $I): void
    {
        $admin = $this->createUser($I);
        $I->amLoggedInAs(User::findByEmail($admin->email));
        $this->url->view(User::SUPER_ID);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    public function failViewAdminByNotSuperAdmin(\FunctionalTester $I): void
    {
        $admin1 = $this->createUser($I);
        $admin2 = $this->createUser($I);
        $I->amLoggedInAs(User::findByEmail($admin1->email));
        $this->url->view($admin2->id);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    public function failViewByAdminFromAnotherCompany(\FunctionalTester $I): void
    {
        $company = $this->createCompany($I);
        $admin = $this->createUser($I, User::ROLE_ADMIN, $company, ['manageUsers' => true]);
        $user1 = $this->createUser($I, User::ROLE_USER, $company);
        $user2 = $this->createUser($I, User::ROLE_USER);
        $I->amLoggedInAs(User::findByEmail($admin->email));
        $this->url->view($user1->id);
        $I->seeResponseCodeIs(HttpCode::OK);
        $this->url->view($user2->id);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    public function failViewAnotherUserByUser(\FunctionalTester $I): void
    {
        $user1 = $this->createUser($I, User::ROLE_USER);
        $user2 = $this->createUser($I, User::ROLE_USER);
        $I->amLoggedInAs(User::findByEmail($user1->email));
        $this->url->view($user2->id);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }
}
