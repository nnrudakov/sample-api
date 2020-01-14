<?php
/** @noinspection PhpPossiblePolymorphicInvocationInspection */
/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\components\behaviors;

use yii\base\Behavior;
use yii\db\{ActiveRecord, ArrayExpression};
use app\models\Access;

/**
 * Поведение сброса разрешений пользователя.
 *
 * Поведение срабатывает после успешного обновления данных пользователя. Если изменяется роль, то все разрешения (и для
 * предыдущих организаций, и для новых) удаляются. Необходимо снова задать разрешения. Если роль не изменяется, но
 * изменяется список разрешённых организаций, то удаляются только те разрешения, по которым были убраны организации.
 *
 * @property bool            $roleChanged  Флаг изменения роли.
 * @property ArrayExpression $oldCompanies Список предыдущих организаций.
 *
 * @package    app\components\behaviors
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 *
 * @see https://www.yiiframework.com/doc/guide/2.0/en/security-authorization#rbac
 */
class ResetPermissionsBehavior extends Behavior
{
    /**
     * @var bool Флаг изменения роли.
     */
    public bool $roleChanged;
    /**
     * @var ArrayExpression|null Список предыдущих организаций.
     */
    public ?ArrayExpression $oldCompanies;

    /**
     * {@inheritdoc}
     */
    public function events(): array
    {
        return [ActiveRecord::EVENT_AFTER_UPDATE => 'run'];
    }

    /**
     * Сброс разрешений.
     */
    public function run(): void
    {
        (new Access(['userId' => $this->owner->id]))->resetPermissions($this->getCompanies());
    }

    /**
     * Возвращает список организаций для сброса.
     *
     * Если изменяется роль, то возвращает полный список. Если роль не изменяется, то только предыдущие организации.
     *
     * @return array
     */
    private function getCompanies(): array
    {
        $model_companies = $this->convertExpressionToArray($this->owner->companies);
        $old_companies = $this->convertExpressionToArray($this->oldCompanies ?: []);

        return $this->roleChanged
            ? \array_unique(\array_merge($model_companies, $old_companies))
            : \array_diff($old_companies, $model_companies);
    }

    /**
     * Преобразование при необходимости объектного представления списка организаций в массив.
     *
     * @param ArrayExpression|array $companies Список организаций.
     *
     * @return array
     */
    private function convertExpressionToArray($companies): array
    {
        return $companies instanceof ArrayExpression ? $companies->getValue() : $companies;
    }
}
