<?php

declare(strict_types=1);

namespace tests\functional\users;

use Codeception\Stub;
use Codeception\Util\HttpCode;
use PHPUnit\Framework\Assert;
use Page\Users as Url;
use tests\functional\BaseCest;
use yii\mail\MessageInterface;
use yii\swiftmailer\Mailer;
use app\models\User;
use app\models\db\Company;

/**
 * Class UsersCreateCest
 *
 * @package    tests\functional\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class UsersCreateCest extends BaseCest
{
    /**
     * @var $url Url
     */
    private Url $url;
    /**
     * @var Company
     */
    private Company $company;

    public function _before(\FunctionalTester $I): void
    {
        parent::_before($I);
        $this->url = new Url($I);
        $I->amLoggedInAs(User::findByEmail('nnrudakov@gmail.com'));
        $this->company = $this->createCompany($I);
    }

    public function createUserSuccess(\FunctionalTester $I): void
    {
        $data = $this->generateUser();
        $data['companies'] = [$this->company->id];
        $this->url->create($data);
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseContainsJson(['email' => $data['email']]);

        $emailMessage = $I->grabLastSentEmail();
        expect('valid email is sent', $emailMessage)->isInstanceOf(MessageInterface::class);
        expect($emailMessage->getTo())->hasKey($data['email']);
    }

    public function emailSendFail(\FunctionalTester $I): void
    {
        \Yii::$app->set('mailer', Stub::make(Mailer::class, [
            'compose' => static function () { throw new \ErrorException('error'); }
        ]));
        $data = $this->generateUser();
        $data['companies'] = [$this->company->id];
        $this->url->create($data);
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $I->seeResponseContainsJson(['email' => $data['email']]);
    }

    public function createUserByNotSuperAdmin(\FunctionalTester $I): void
    {
        $data = $this->generateUser();
        $data['companies'] = [$this->company->id];
        $this->url->create($data);
        $I->seeResponseCodeIs(HttpCode::CREATED);

        $I->amLoggedInAs(User::findByEmail($data['email']));
        $data = $this->generateUser();
        $data['companies'] = [$this->company->id];
        $this->url->create($data);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    public function addNotExistsCompany(\FunctionalTester $I): void
    {
        $data = $this->generateUser();
        $data['companies'] = [-1];
        $this->url->create($data);
        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
    }

    public function onlyOneCompanyForUser(\FunctionalTester $I): void
    {
        $company = $this->createCompany($I);
        $data = $this->generateUser(User::ROLE_USER);
        $data['companies'] = [$this->company->id, $company->id];
        $this->url->create($data);
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $id = $I->grabDataFromResponseByJsonPath('$.id')[0];
        $user = $I->grabRecord(User::class, ['id' => $id]);
        Assert::assertNotContains($company->id, $user['companies']);
    }

    public function addForbiddenCompany(\FunctionalTester $I): void
    {
        $invalid_company = $this->createCompany($I);
        $data = $this->generateUser();
        $data['companies'] = [$this->company->id];
        $this->url->create($data);
        $I->seeResponseCodeIs(HttpCode::CREATED);

        $admin = User::findByEmail($data['email']);
        $this->createAccess($I, $this->company, $admin, ['manageUsers' => true]);

        $I->amLoggedInAs($admin);
        $data = $this->generateUser(User::ROLE_USER);
        $data['companies'] = [$this->company->id];
        $this->url->create($data);
        $I->seeResponseCodeIs(HttpCode::CREATED);

        $data = $this->generateUser(User::ROLE_USER);
        $data['companies'] = [$invalid_company->id, $this->company->id];
        $this->url->create($data);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);

        $data['companies'] = [$this->company->id, $invalid_company->id];
        $this->url->create($data);
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $user = User::findByEmail($data['email']);
        Assert::assertContains($this->company->id, $user->companies);
        Assert::assertNotContains($invalid_company->id, $user->companies);
    }
}
