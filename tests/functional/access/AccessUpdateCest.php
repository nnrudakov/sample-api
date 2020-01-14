<?php

declare(strict_types=1);

namespace tests\functional\access;

use Codeception\Util\HttpCode;
use Page\Access as Url;
use PHPUnit\Framework\Assert;
use tests\functional\BaseCest;
use app\models\User;

/**
 * Class AccessUpdateCest
 *
 * @package    tests\functional\access
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class AccessUpdateCest extends BaseCest
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

    public function failWithoutParams(\FunctionalTester $I): void
    {
        $this->url->update(1, 1, []);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function failUpdateSuperAdmin(\FunctionalTester $I): void
    {
        $this->url->update(1, User::SUPER_ID, ['perm' => true]);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    public function updateSuccess(\FunctionalTester $I): void
    {
        $company = $this->createCompany($I);
        $user = $this->createUser($I, User::ROLE_USER, $company, ['viewEconomic' => false]);
        $access = $this->generateAccess(['manageUsers' => true, 'viewEconomic' => false]);
        unset($access['viewDevices']);
        $this->url->update($company->id, $user->id, $access);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $this->url->view($company->id, $user->id);
        $new_access = \json_decode($I->grabResponse(), true, 512, \JSON_THROW_ON_ERROR);
        foreach ($new_access as $key => $value) {
            $key === 'viewDevices' ? Assert::assertFalse($value) : Assert::assertEquals($access[$key], $value);
        }
    }

    public function updateAdminByAdmin(\FunctionalTester $I): void
    {
        $admin = $this->createUser($I);
        $I->amLoggedInAs(User::findByEmail($admin->email));
        $this->url->view(1, User::SUPER_ID);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);

        $this->url->view(1, $admin->id);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    public function failUpdateUserFromAnotherCompany(\FunctionalTester $I): void
    {
        $company = $this->createCompany($I);
        $admin = $this->createUser($I, User::ROLE_ADMIN, $company, ['manageUsers' => true]);
        $user = $this->createUser($I, User::ROLE_USER);
        $I->amLoggedInAs(User::findByEmail($admin->email));

        $this->url->update($company->id, $user->id, $this->generateAccess());
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    private function generateAccess(array $permissions = []): array
    {
        return [
            'manageUsers'      => $permissions['manageUsers'] ?? $this->faker->boolean(70),
            'viewDevices'      => $permissions['viewDevices'] ?? $this->faker->boolean(70),
            'manageDevices'    => $permissions['manageDevices'] ?? $this->faker->boolean(70),
            'viewEquipments'   => $permissions['viewEquipments'] ?? $this->faker->boolean(70),
            'manageEquipments' => $permissions['manageEquipments'] ?? $this->faker->boolean(70),
            'managePlacements' => $permissions['managePlacements'] ?? $this->faker->boolean(70),
            'manageLines'      => $permissions['manageLines'] ?? $this->faker->boolean(70),
            'manageExtFactors' => $permissions['manageExtFactors'] ?? $this->faker->boolean(70),
            'enterMetrics'     => $permissions['enterMetrics'] ?? $this->faker->boolean(70),
            'manageTariffs'    => $permissions['manageTariffs'] ?? $this->faker->boolean(70),
            'viewEconomic'     => $permissions['viewEconomic'] ?? $this->faker->boolean(70),
        ];
    }
}
