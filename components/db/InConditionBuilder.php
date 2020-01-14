<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\components\db;

use yii\db\ExpressionInterface;

/**
 * Построитель запросов по условию вхождения в список.
 *
 * Переопределяет стандартный построитель. Атрибут списка организаций пользователя является массивом PostgreSQL, поэтому
 * обычный оператор `IN` к нему неприменим. Поэтому для данного атрибута используется отдельный синтаксис для данной
 * БД.
 *
 * @package    app\components\db
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class InConditionBuilder extends \yii\db\conditions\InConditionBuilder
{
    public function build(ExpressionInterface $expression, array &$params = []): string
    {
        return $expression->getColumn() === 'companies'
            ? 'companies @> ARRAY[' . \implode(',', $expression->getValues()) . ']'
            : parent::build($expression, $params);
    }
}
