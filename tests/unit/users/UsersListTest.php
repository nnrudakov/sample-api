<?php

declare(strict_types=1);

namespace tests\unit\users;

use Codeception\Stub;
use tests\unit\BaseUnit;
use yii\data\{ActiveDataFilter, ActiveDataProvider};
use yii\rest\Controller;
use yii\web\{ForbiddenHttpException, User as WebUser};
use app\controllers\exceptions\InvalidParamException;
use app\controllers\users\IndexAction;
use app\models\db\{Company, User};
use app\models\search\UserSearch;

/**
 * Class UsersListTest
 *
 * @package    tests\unit\users
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class UsersListTest extends BaseUnit
{
    private Company $company1;
    private Company $company2;
    private User $admin;
    private User $user1;
    private User $user2;

    public function testSeeMissedRoleFilter(): void
    {
        \Yii::$app->request->setQueryParams(['filter' => ['companies' => '1']]);
        /** @var IndexAction $action */
        $action = \Yii::createObject(IndexAction::class, [
            'index',
            Stub::make(Controller::class),
            ['modelClass' => UserSearch::class, 'request' => \Yii::$app->request],
        ]);
        $this->expectException(InvalidParamException::class);
        $action->runWithParams([]);
    }

    public function testSeeMissedCompanyFilter(): void
    {
        \Yii::$app->request->setQueryParams(['filter' => ['role' => User::ROLE_ADMIN]]);
        /** @var IndexAction $action */
        $action = \Yii::createObject(IndexAction::class, [
            'index',
            Stub::make(Controller::class),
            ['modelClass' => UserSearch::class, 'request' => \Yii::$app->request],
        ]);
        $this->expectException(InvalidParamException::class);
        $action->runWithParams([]);
    }

    public function testValidateErrors(): void
    {
        $user = User::findByEmail('nnrudakov@gmail.com');
        \Yii::$app->request->setQueryParams(['filter' => ['role' => 'wrong_role', 'companies' => '1']]);
        /** @var IndexAction $action */
        $action = \Yii::createObject(IndexAction::class, [
            'index',
            Stub::make(Controller::class),
            [
                'modelClass' => UserSearch::class, 'request' => \Yii::$app->request, 'user' => $user,
                'dataFilter' => ['class' => ActiveDataFilter::class, 'searchModel' => UserSearch::class]
            ]
        ]);
        $this->assertInstanceOf(ActiveDataFilter::class, $result = $action->run());
    }

    public function testSeeListBySuperAdmin(): void
    {
        $this->generateFixtures();
        $user = User::findByEmail('nnrudakov@gmail.com');
        \Yii::$app->request->setQueryParams(['filter' => [
            'role' => User::ROLE_ADMIN, 'companies' => $this->company1->id]
        ]);
        /** @var IndexAction $action */
        $action = \Yii::createObject(IndexAction::class, [
            'index',
            Stub::make(Controller::class),
            [
                'modelClass' => UserSearch::class, 'request' => \Yii::$app->request, 'user' => $user,
                'dataFilter' => ['class' => ActiveDataFilter::class, 'searchModel' => UserSearch::class]
            ]
        ]);
        /** @var ActiveDataProvider $provider */
        $provider = $action->runWithParams([]);
        $this->assertNotEmpty($provider->models);
        /** @var User $model */
        foreach ($provider->models as $model) {
            $this->assertNotEquals($this->user1->id, $model->id);
        }
    }

    public function testCantSeeAdminsByAdmin(): void
    {
        $this->generateFixtures();
        \Yii::$app->request->setQueryParams(['filter' => [
            'role' => User::ROLE_ADMIN, 'companies' => ['in' => [$this->company1->id]]]
        ]);
        /** @var IndexAction $action */
        $action = \Yii::createObject(IndexAction::class, [
            'index',
            Stub::make(Controller::class),
            [
                'modelClass' => UserSearch::class, 'request' => \Yii::$app->request, 'user' => $this->admin,
                'dataFilter' => ['class' => ActiveDataFilter::class, 'searchModel' => UserSearch::class]
            ]
        ]);
        $this->expectException(ForbiddenHttpException::class);
        $action->runWithParams([]);
    }

    public function testSeeListByAdminCorrectCompany(): void
    {
        $this->generateFixtures();
        \Yii::$app->set('user', Stub::make(WebUser::class, ['can' => static function () { return true; }]));
        \Yii::$app->request->setQueryParams(['filter' => [
            'role' => User::ROLE_USER, 'companies' => ['in' => [$this->company1->id]]]
        ]);
        /** @var IndexAction $action */
        $action = \Yii::createObject(IndexAction::class, [
            'index',
            Stub::make(Controller::class),
            [
                'modelClass' => UserSearch::class, 'request' => \Yii::$app->request, 'webUser' => \Yii::$app->user,
                'user' => $this->admin, 'dataFilter' => ['class' => ActiveDataFilter::class, 'searchModel' => UserSearch::class]
            ]
        ]);
        /** @var ActiveDataProvider $provider */
        $provider = $action->runWithParams([]);
        $this->assertNotEmpty($provider->models);
        /** @var User $model */
        foreach ($provider->models as $model) {
            $this->assertNotEquals($this->user2->id, $model->id);
        }
    }

    public function testSeeListByAdminWrongCompany(): void
    {
        $this->generateFixtures();
        \Yii::$app->set('user', Stub::make(WebUser::class, ['can' => static function () { return true; }]));
        \Yii::$app->request->setQueryParams(['filter' => [
            'role' => User::ROLE_USER, 'companies' => ['in' => [$this->company2->id]]]
        ]);
        /** @var IndexAction $action */
        $action = \Yii::createObject(IndexAction::class, [
            'index',
            Stub::make(Controller::class),
            [
                'modelClass' => UserSearch::class, 'request' => \Yii::$app->request, 'webUser' => \Yii::$app->user,
                'user' => $this->admin, 'dataFilter' => ['class' => ActiveDataFilter::class, 'searchModel' => UserSearch::class]
            ]
        ]);
        /** @var ActiveDataProvider $provider */
        $provider = $action->runWithParams([]);
        $this->assertEmpty($provider->models);
    }

    private function generateFixtures(): void
    {
        $this->company1 = $this->generateCompany();
        $this->company2 = $this->generateCompany();
        $this->admin = $this->generateUser(User::ROLE_ADMIN, $this->company1);
        $this->user1 = $this->generateUser(User::ROLE_USER, $this->company1);
        $this->user2 = $this->generateUser(User::ROLE_USER, $this->company2);
    }
}
