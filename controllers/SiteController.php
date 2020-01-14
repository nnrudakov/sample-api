<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\filters\{AccessControl, AjaxFilter, Cors, VerbFilter};
use yii\rest\Controller;

/**
 * Контроллер общих действий.
 *
 * @package    app\controllers
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'corsFilter' => ['class' => Cors::class,
                'cors' => Yii::$app->params['cors']
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['csrf'],
                        'allow' => true,
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'csrf' => ['HEAD'],
                ],
            ],
            'ajax' => [
                'class' => AjaxFilter::class
            ],
        ];
    }

    /**
     * @OA\Head(
     *     path="/csrf",
     *     operationId="csrf",
     *     summary="Получение CSRF токена",
     *     description="Устанавливает значение `CSRF` токена в заголовке `X-Csrf-Token`. Токен должен использоваться в заголовках всех запросов на запись.",
     *     @OA\Response(
     *          response="204",
     *          description="Установка токена в заголовке",
     *          @OA\Schema(type="string"),
     *          @OA\Header(
     *              header="X-Csrf-Token",
     *              description="CSRF токен",
     *              @OA\Schema(type="string"),
     *          ),
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Ошибка",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    /**
     * Устанавливает CSRF токен в заголовок.
     *
     * Для предотвращения `CSRF` атак используется защитный токен. Все запросы на запись (`POST`, `DELETE` и др.)
     * должны передавать этот токен в заголовке `X-Csrf-Token`.Токен создаётся один на каждый запрос. Например, если
     * вызвать метод два раза, но во время `POST` запроса отправить первый токен, то будет возвращена ошибка о неверном
     * токене, потому что действительным будет второй токен, который был создан позже. Для каждого пользователя
     * создаётся свой токен, поэтому его нельзя кешировать.
     */
    public function actionCsrf(): void
    {
        Yii::$app->response->setStatusCode(204)
            ->getHeaders()->add('X-Csrf-Token', Yii::$app->request->getCsrfToken(true));
    }
}
