<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\controllers\companies;

use yii\caching\TagDependency;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use app\controllers\{ActionTrait, CompaniesController};
use app\models\db\User;

/**
 * Действие получения списка организаций.
 *
 * Возвращает список организаций с учётом параметров фильтрации, сортировки и пагинации.
 *
 * @property bool $isSuperAdmin Флаг главного администратора. Установка означает, что вернуть список всех организаций.
 *                              В остальных случаях возвращается список только тех организаций, в которых у пользователя
 *                              есть доступ.
 *
 * @package    app\controllers\companies
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class IndexAction extends \yii\rest\IndexAction
{
    use ActionTrait;

    /**
     * @var bool Флаг главного администратора.
     */
    public bool $isSuperAdmin = false;

    /**
     * @OA\Get(
     *     path="/companies",
     *     operationId="companies-get-list",
     *     tags={"Организации"},
     *     summary="Получение списка организаций",
     *     description="Возвращает список организаций.<br><br>Список возвращается в соответствии с правилами доступа.<br><br>См. [работу со списком](#section/Spiski).<br><br>Фильтрация списка не предоставляется. Сортировка по умолчанию по наименованию организации в алфавитном порядке.<br><br>Список доступных атрибутов см. в [информации об организации](#operation/companies-get-one).",
     *     @OA\Response(
     *          response="200",
     *          description="Список организаций",
     *          @OA\Header(
     *              header="X-Pagination-Total-Count",
     *              ref="#/components/headers/X-Pagination-Total-Count"
     *          ),
     *          @OA\Header(
     *              header="X-Pagination-Page-Count",
     *              ref="#/components/headers/X-Pagination-Page-Count"
     *          ),
     *          @OA\Header(
     *              header="X-Pagination-Current-Page",
     *              ref="#/components/headers/X-Pagination-Current-Page"
     *          ),
     *          @OA\Header(
     *              header="X-Pagination-Per-Page",
     *              ref="#/components/headers/X-Pagination-Per-Page"
     *          ),
     *          @OA\Header(
     *              header="Link",
     *              ref="#/components/headers/Link"
     *          ),
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/CompanyShort")
     *          ),
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Ошибка",
     *         @OA\JsonContent(ref="#/components/schemas/Error")
     *     )
     * )
     */
    /** @noinspection ClassMethodNameMatchesFieldNameInspection */
    /**
     * {@inheritdoc}
     */
    protected function prepareDataProvider(): ActiveDataProvider
    {
        $provider = parent::prepareDataProvider();
        $provider->query->alias('c');
        $provider->query->select(['c.id', 'title', 'inn', 'address', 'phone', 'person', 'contact', 'c.enabled']);
        if (!$this->isSuperAdmin) {
            $provider->query->innerJoin(User::tableName() . ' u', new Expression('c.id=ANY(u.companies)'));
        }
        $provider->query->cache(2592000, new TagDependency(['tags' => CompaniesController::COMPANIES_CACHE]));
        $provider->sort->defaultOrder = ['title' => \SORT_ASC];

        return $provider;
    }
}
