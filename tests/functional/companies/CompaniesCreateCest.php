<?php

declare(strict_types=1);

namespace tests\functional\companies;

use Codeception\Util\HttpCode;
use Page\{Companies as Url, Users};
use tests\functional\BaseCest;
use app\models\User;

/**
 * Class CompaniesCreateCest
 *
 * @package    tests\functional\companies
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class CompaniesCreateCest extends BaseCest
{
    /**
     * @var $url Url
     */
    private Url$url;

    public function _before(\FunctionalTester $I): void
    {
        parent::_before($I);
        $this->url = new Url($I);
        $I->amLoggedInAs(User::findByEmail('nnrudakov@gmail.com'));
    }

    public function invalidPhone(\FunctionalTester $I): void
    {
        $data = $this->generateCompany();
        $data['phone'] = 'wrong_phone';
        $this->url->create($data);
        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
    }

    public function createCompanySuccess(\FunctionalTester $I): void
    {
        $data = $this->generateCompany();
        $this->url->create($data);
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseContainsJson(['title' => $data['title']]);
    }

    public function createByNotSuperAdmin(\FunctionalTester $I): void
    {
        $company = $this->createCompany($I);
        $user = $this->generateUser();
        $user['companies'] = [$company->id];
        $users_url = new Users($I);
        $users_url->create($user);
        $I->seeResponseCodeIs(HttpCode::CREATED);

        $I->amLoggedInAs(User::findByEmail($user['email']));
        $data = $this->generateCompany();
        $this->url->create($data);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }
}
