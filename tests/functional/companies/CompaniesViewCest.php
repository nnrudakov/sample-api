<?php

declare(strict_types=1);

namespace tests\functional\companies;

use Codeception\Util\HttpCode;
use Page\Companies as Url;
use tests\functional\BaseCest;
use app\models\User;

/**
 * Class CompaniesViewCest
 *
 * @package    tests\functional\companies
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class CompaniesViewCest extends BaseCest
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

    public function getCompany(\FunctionalTester $I): void
    {
        $data = $this->generateCompany();
        $this->url->create($data);
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $id = $I->grabDataFromResponseByJsonPath('$.id')[0];

        $this->url->view($id);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['id' => $id]);
        $I->dontSeeResponseContains('scada_password');

        $this->url->view($id, ['expand' => 'scada_password']);
        $I->seeResponseContains('scada_password');
    }

    public function adminsCantSeeScadaAccess(\FunctionalTester $I): void
    {
        $company = $this->createCompany($I);
        $admin = $this->createUser($I, User::ROLE_ADMIN, $company);
        $I->amLoggedInAs(User::findByEmail($admin->email));
        $this->url->view($company->id, ['expand' => 'scada_password']);
        $I->dontSeeResponseContains('scada_password');
    }
}
