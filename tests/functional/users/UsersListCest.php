<?php

declare(strict_types=1);

namespace tests\functional\users;

use Codeception\Util\HttpCode;
use Page\Users as Url;
use tests\functional\BaseCest;
use app\models\User;

/**
 * Class UsersListCest
 *
 * @package    tests\functional\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class UsersListCest extends BaseCest
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

    public function failListByAdminWithoutPermission(\FunctionalTester $I): void
    {
        $admin = $this->createUser($I);
        $I->amLoggedInAs(User::findByEmail($admin->email));
        $this->url->list(['filter' => ['role' => 'role', 'companies' => 1]]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }
}
