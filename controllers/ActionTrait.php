<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\controllers;

use Yii;
use yii\caching\TagDependency;
use yii\web\{Request, Response};

/**
 * Типаж общих свойств и методов для действий.
 *
 * Введён, чтобы добавить единый функционал и для действий приложения, и для стандартных методов `Yii`. В последнем
 * случае действие должно быть переопределено с добавлением данного типажа.
 *
 * Свойства `log*` используются для журналирования действий пользователя.
 *
 * Типаж должны наследовать все действия, которые переопределяют стандартные методы `Yii`.
 *
 * @property Request  $request  Объект запроса.
 * @property Response $response Объект ответа.
 *
 * @package    app\controllers
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
trait ActionTrait
{
    /**
     * @var Request Объект запроса.
     */
    public Request $request;
    /**
     * @var Response Объект ответа.
     */
    public Response $response;

    /**
     * @var integer Идентификатор авторизованного пользователя.
     */
    protected ?int $logUserId = null;
    /**
     * @var integer Идентификатор организации пользователя.
     */
    protected ?int $logCompanyId = null;
    /**
     * @var string Имя действия для журнала. По умолчанию идентификатор действия.
     */
    protected ?string $logAction = null;
    /**
     * @var string Идентификатор или заголовок объекта действия.
     */
    protected ?string $logObject = null;

    /**
     * Возвращает идентификатор пользователя, выполняющий действие.
     *
     * @return int|null
     */
    public function getLogUserId(): ?int
    {
        return $this->logUserId;
    }

    /**
     * Возвращает идентификатор организации, в которой выполняется действие.
     *
     * @return int|null
     */
    public function getLogCompanyId(): ?int
    {
        return $this->logCompanyId;
    }

    /**
     * Возвращает идентификатор действия.
     *
     * @return string|null
     */
    public function getLogActionId(): ?string
    {
        return $this->logAction;
    }

    /**
     * Возвращает идентификатор идентификатор или заголовок объекта действия.
     *
     * @return string|null
     */
    public function getLogObject(): ?string
    {
        return $this->logObject;
    }

    /**
     * Очистка данных кеша по тегу.
     *
     * @param string $tag Имя тега.
     */
    protected function invalidateCache(string $tag): void
    {
        TagDependency::invalidate(Yii::$app->cache, $tag);
    }
}
