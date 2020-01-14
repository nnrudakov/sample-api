<?php

declare(strict_types=1);

namespace tests\functional;

use Codeception\Util\HttpCode;
use Faker\{Factory, Generator};
use Page\{Access, Companies, Users};
use app\models\User;
use app\models\db\Company;

/**
 * Class BaseCest
 *
 * @package    tests\functional
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
abstract class BaseCest
{
    /**
     * @var Generator
     */
    protected Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create('ru_RU');
    }

    public function _before(\FunctionalTester $I): void
    {
        $I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');
    }

    protected function createUserModel(\FunctionalTester $I, $role = User::ROLE_ADMIN): User
    {
        $attributes = $this->generateUser($role);

        return $I->grabRecord(User::class, ['id' => $I->haveRecord(User::class, $attributes)]);
    }

    protected function createUser(\FunctionalTester $I, $role = User::ROLE_ADMIN, Company $company = null, array $permissions = []): User
    {
        if (!$company) {
            $company = $this->createCompany($I);
        }
        $attributes = $this->generateUser($role);
        $attributes['companies'] = [$company->id];
        $url = new Users($I);
        $url->create($attributes);
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $id = $I->grabDataFromResponseByJsonPath('$.id')[0];
        $user = $I->grabRecord(User::class, ['id' => $id]);
        if ($permissions) {
            $this->createAccess($I, $company, $user, $permissions);
        }

        return $user;
    }

    protected function generateUser(string $role = User::ROLE_ADMIN): array
    {
        return [
            'email'     => $this->faker->email,
            'name'      => $this->faker->name,
            'role'      => $role,
            'enabled'   => true,
            'companies' => null,
        ];
    }

    protected function generateCompany(): array
    {
        return [
            'title'          => $this->faker->title,
            'inn'            => $this->faker->isbn10,
            'ogrn'           => $this->faker->isbn13,
            'address'        => $this->faker->word,
            'phone'          => '+79999999999',
            'person'         => $this->faker->name,
            'contact'        => $this->faker->word,
            'enabled'        => true,
            'scada_host'     => $this->faker->domainName,
            'scada_port'     => $this->faker->randomNumber(5),
            'scada_db'       => $this->faker->word,
            'scada_user'     => $this->faker->userName,
            'scada_password' => $this->faker->password
        ];
    }

    protected function createCompany(\FunctionalTester $I): Company
    {
        $url = new Companies($I);
        $data = $this->generateCompany();
        $url->create($data);
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $id = $I->grabDataFromResponseByJsonPath('$.id')[0];

        return $I->grabRecord(Company::class, ['id' => $id]);
    }

    protected function createAccess(\FunctionalTester $I, Company $company, User $user, array $permissions): void
    {
        $url = new Access($I);
        $url->update($company->id, $user->id, $permissions);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }
}
