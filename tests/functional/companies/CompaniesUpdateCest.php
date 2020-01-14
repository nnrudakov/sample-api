<?php

declare(strict_types=1);

namespace tests\functional\companies;

use Codeception\Util\HttpCode;
use Page\Companies as Url;
use tests\functional\BaseCest;
use app\models\User;

/**
 * Class CompaniesUpdateCest
 *
 * @package    tests\functional\companies
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class CompaniesUpdateCest extends BaseCest
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

    public function updateCompany(\FunctionalTester $I): void
    {
        $data = $this->generateCompany();
        $this->url->create($data);
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $id = $I->grabDataFromResponseByJsonPath('$.id')[0];

        $this->url->update($id, ['title' => $this->faker->title]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }
}
