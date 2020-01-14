<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\filters\{AccessControl, VerbFilter};
use yii\helpers\ArrayHelper;
use app\controllers\access\{ViewAction, UpdateAction};
use app\models\Access;
use app\models\db\User;

/**
 * @OA\Tag(
 *     name="Разрешения",
 *     description="Операции с разрешениями пользователей",
 * )
 */
/**
 * Контроллер управления разрешениями пользователей.
 *
 * @package    app\controllers
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class AccessController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public $modelClass = Access::class;

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['view', 'update'],
                        'allow' => true,
                        'roles' => [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'view' => ['GET'],
                    'update' => ['PATCH'],
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['create'], $actions['delete']);
        $actions['view']['class'] = ViewAction::class;
        $actions['view']['findModel'] = $actions['update']['findModel'] = null;
        $actions['view']['webUser'] = $actions['update']['webUser'] = $this->webUser;
        $actions['view']['user'] = $actions['update']['user'] = $this->user;
        $actions['update']['class'] = UpdateAction::class;
        $actions['update']['request'] = $this->request;
        $actions['update']['response'] = $this->response;
        unset($actions['update']['scenario']);

        return $actions;
    }
}
