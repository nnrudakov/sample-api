<?php

declare(strict_types=1);

namespace tests\unit;

use Codeception\Test\Unit;
use Faker\{Factory, Generator};
use app\models\db\{Company, User};

/**
 * Class BaseUnit
 *
 * @property \UnitTester $tester Unit $tester.
 *
 * @package    tests\unit
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
abstract class BaseUnit extends Unit
{
    /**
     * @var Generator
     */
    protected Generator $faker;

    protected function _before(): void
    {
        parent::_before();

        $this->faker = Factory::create('ru_RU');
    }

    protected function generateUser(string $role = 'admin', Company $company = null): User
    {
        if (!$company) {
            $company = $this->generateCompany();
        }
        $user = new User([
            'email'         => $this->faker->email,
            'name'          => $this->faker->name,
            'password_hash' => \Yii::$app->security->generatePasswordHash($this->faker->password),
            'role'          => $role,
            'enabled'       => true,
            'companies'     => [$company->id],
        ]);
        $user->save();
        verify('User not saved. ' . print_r($user->getErrors(), true), $user->id)->notEmpty();

        return $user;
    }

    protected function generateCompany(bool $enabled = true): Company
    {
        $company = new Company([
            'title'          => $this->faker->title,
            'inn'            => $this->faker->isbn10,
            'ogrn'           => $this->faker->isbn13,
            'address'        => $this->faker->word,
            'phone'          => '+79999999999',
            'person'         => $this->faker->name,
            'contact'        => $this->faker->word,
            'enabled'        => $enabled,
            'scada_host'     => $this->faker->domainName,
            'scada_port'     => $this->faker->randomNumber(5),
            'scada_db'       => $this->faker->word,
            'scada_user'     => $this->faker->userName,
            'scada_password' => $this->faker->password
        ]);
        $company->save();
        verify('Company not saved. ' . print_r($company->getErrors(), true), $company->id)->notEmpty();

        return $company;
    }
}
