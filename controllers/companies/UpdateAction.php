<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\controllers\companies;

use app\controllers\{ActionTrait, CompaniesController};
use app\models\db\Company;

/**
 * Действие обновления данных организации.
 *
 * Получает новые данные организации и обновляет.
 *
 * @package    app\controllers\companies
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class UpdateAction extends \yii\rest\UpdateAction
{
    use ActionTrait;

    /**
     * @OA\Patch(
     *     path="/companies/{id}",
     *     operationId="companies-update",
     *     tags={"Организации"},
     *     summary="Обновление организации",
     *     description="Получает новые данные организации и обновляет.",
     *     @OA\Parameter(
     *         name="X-Csrf-Token",
     *         in="header",
     *         description="CSRF токен",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Идентификатор организации",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         description="Новые данные",
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                      property="title",
     *                      ref="#/components/schemas/title"
     *                  ),
     *                  @OA\Property(
     *                      property="inn",
     *                      ref="#/components/schemas/inn"
     *                  ),
     *                  @OA\Property(
     *                      property="ogrn",
     *                      ref="#/components/schemas/ogrn"
     *                  ),
     *                  @OA\Property(
     *                      property="address",
     *                      ref="#/components/schemas/address"
     *                  ),
     *                  @OA\Property(
     *                      property="phone",
     *                      ref="#/components/schemas/phone"
     *                  ),
     *                  @OA\Property(
     *                      property="person",
     *                      ref="#/components/schemas/person"
     *                  ),
     *                  @OA\Property(
     *                      property="contact",
     *                      ref="#/components/schemas/contact"
     *                  ),
     *                   @OA\Property(
     *                      property="enabled",
     *                      title="Активна",
     *                      type="boolean"
     *                  ),
     *                  @OA\Property(
     *                      property="scada_host",
     *                      ref="#/components/schemas/scada_host"
     *                  ),
     *                  @OA\Property(
     *                      property="scada_port",
     *                      ref="#/components/schemas/scada_port"
     *                  ),
     *                  @OA\Property(
     *                      property="scada_db",
     *                      ref="#/components/schemas/scada_db"
     *                  ),
     *                  @OA\Property(
     *                      property="scada_user",
     *                      ref="#/components/schemas/scada_user"
     *                  ),
     *                  @OA\Property(
     *                      property="scada_password",
     *                      ref="#/components/schemas/scada_password"
     *                  ),
     *                  @OA\Property(
     *                      property="comment",
     *                      ref="#/components/schemas/comment"
     *                  ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *          response="204",
     *          description="Данные успешно изменены"
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Неверный CSRF токен",
     *          @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Организация не найдена",
     *          @OA\JsonContent(ref="#/components/schemas/Error")
     *     ),
     *     @OA\Response(
     *          response="422",
     *          description="Ошибки валидации данных",
     *          @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ValidateError")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Ошибка",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    /**
     * {@inheritdoc}
     */
    public function run($id): Company
    {
        /** @var Company $company */
        $company = parent::run($id);
        $this->logObject = $company->title;
        if (!$company->hasErrors()) {
            $this->invalidateCache(CompaniesController::COMPANIES_CACHE);
        }
        $this->response->setStatusCode(204, 'Company Has Been Updated');

        return $company;
    }
}
