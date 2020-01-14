<?php

declare(strict_types=1);

namespace tests\functional\users;

use Codeception\Util\HttpCode;
use Page\Users as Url;
use tests\functional\BaseCest;
use app\models\User;

/**
 * Class UsersUpdateCest
 *
 * @package    tests\functional\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class UsersUpdateCest extends BaseCest
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

    public function userNotFound(\FunctionalTester $I): void
    {
        $this->url->update(1000000000, ['name' => $this->faker->name]);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    public function updateSelfUser(\FunctionalTester $I): void
    {
        $this->url->update(User::SUPER_ID, ['name' => $this->faker->name]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }

    public function updateAnotherUserBySuperAdmin(\FunctionalTester $I): void
    {
        $user = $this->createUser($I);
        $this->url->update($user->id, ['name' => $this->faker->name]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }

    public function updateAnotherUserByAdmin(\FunctionalTester $I): void
    {
        $company = $this->createCompany($I);
        $admin = $this->createUser($I, User::ROLE_ADMIN, $company, ['manageUsers' => true]);
        $user = $this->createUser($I, User::ROLE_USER, $company);
        $I->amLoggedInAs(User::findByEmail($admin->email));
        $this->url->update($user->id, ['companies' => [$company->id]]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }

    public function updateSuperAdminByAdmin(\FunctionalTester $I): void
    {
        $user = $this->createUser($I);
        $I->amLoggedInAs(User::findByEmail($user->email));
        $this->url->update(User::SUPER_ID, ['name' => $this->faker->name]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    public function failUpdateAdminByAdmin(\FunctionalTester $I): void
    {
        $company = $this->createCompany($I);
        $admin1 = $this->createUser($I, User::ROLE_ADMIN, $company);
        $admin2 = $this->createUser($I, User::ROLE_ADMIN, $company);
        $I->amLoggedInAs(User::findByEmail($admin1->email));
        $this->url->update($admin2->id, ['name' => $this->faker->name]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    public function failUpdateUserToUnavailableCompany(\FunctionalTester $I): void
    {
        $company1 = $this->createCompany($I);
        $company2 = $this->createCompany($I);
        $admin = $this->createUser($I, User::ROLE_ADMIN, $company1);
        $user = $this->createUser($I, User::ROLE_USER, $company1);
        $I->amLoggedInAs(User::findByEmail($admin->email));
        $this->url->update($user->id, ['name' => $this->faker->name]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);

        $this->url->update($user->id, ['companies' => [$company2->id]]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    public function failUpdateUserByUser(\FunctionalTester $I): void
    {
        $company = $this->createCompany($I);
        $admin = $this->createUser($I, User::ROLE_ADMIN, $company);
        $user = $this->createUser($I, User::ROLE_USER, $company);
        $I->amLoggedInAs(User::findByEmail($user->email));
        $this->url->update($admin->id, ['name' => $this->faker->name]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    public function updateUserAndResetPermissions(\FunctionalTester $I): void
    {
        $company1 = $this->createCompany($I);
        $company2 = $this->createCompany($I);
        $admin = $this->createUser($I, User::ROLE_ADMIN, $company1);
        $this->url->update($admin->id, ['companies' => $company2->id]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }
}
