<?php

/**
 * Copyright (c) 2020. Nikolaj Rudakov
 */

declare(strict_types=1);

namespace app\components;

use Yii;
use yii\web\{ErrorHandler as BaseHandler, BadRequestHttpException, ForbiddenHttpException, NotFoundHttpException};

/**
 * @OA\Schema(
 *     schema="Error",
 *     title="Ошибка",
 *     @OA\Property(
 *          property="name",
 *          title="Имя ошибки",
 *          description="Имя ошибки может использоваться в заголовках диалоговых окон.",
 *          type="string",
 *     ),
 *     @OA\Property(
 *          property="message",
 *          title="Текст ошибки",
 *          description="Текст ошибки для пользователя.",
 *          type="string",
 *     ),
 *     @OA\Property(
 *          property="code",
 *          title="Код ошибки",
 *          description="Код ошибки внутри системы.<br>0 — Неизвестная ошибка;<br>1 — Объект не найден;<br>2 — Неверный CSRF токен;<br>3 — Неверные параметры запроса;<br>4 — Ошибка входа/выхода в систему;<br>5 — Доступ запрещён",
 *          type="integer",
 *          format="int32",
 *          enum={0, 1, 2, 3, 4, 5}
 *     ),
 *     @OA\Property(
 *          property="status",
 *          title="HTTP статус",
 *          description="[HTTP](https://en.wikipedia.org/wiki/List_of_HTTP_status_codes) статус ошибки.",
 *          type="integer",
 *          format="int32"
 *     ),
 *     @OA\Property(
 *          property="type",
 *          title="Исключение сервера",
 *          description="Исключение сервера, которое вызвало ошибку. Присутствует только в режиме разработки.",
 *          type="string",
 *     )
 * )
 * @OA\Schema(
 *     schema="ValidateError",
 *     title="Ошибки валидации",
 *     @OA\Property(
 *          property="field",
 *          title="Имя поля",
 *          description="Имя поля, в котором произошла ошибка.",
 *          type="string",
 *     ),
 *     @OA\Property(
 *          property="message",
 *          title="Текст ошибки",
 *          description="Текст ошибки для пользователя.",
 *          type="string",
 *     )
 * )
 */
/**
 * Обработчик ошибок.
 *
 * @package    app\components
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class ErrorHandler extends BaseHandler
{
    /**
     * Код неизвестной ошибки.
     *
     * @var integer
     */
    public const UNKNOWN = 0;
    /**
     * Код ошибки если что-то (страница, объект) не найдено.
     *
     * @var integer
     */
    public const NOT_FOUND = 1;
    /**
     * Код ошибки неверного CSRF токена.
     *
     * @var integer
     */
    public const INVALID_CSRF = 2;
    /**
     * Код ошибки неверных параметров запроса.
     *
     * @var integer
     */
    public const INVALID_PARAMS = 3;
    /**
     * Код ошибки входа в систему.
     *
     * @var integer
     */
    public const INVALID_LOGIN = 4;
    /**
     * Код ошибки запрета доступа.
     *
     * @var integer
     */
    public const FORBIDDEN = 5;

    /**
     * {@inheritdoc}
     */
    protected function convertExceptionToArray($exception): array
    {
        $array = parent::convertExceptionToArray($exception);
        $array['code'] = $this->getCodeByException($exception);
        $this->regenerateCsrfToken();

        return $array;
    }

    /**
     * Возвращает код ошибки.
     *
     * @param \Exception|\Error $exception Исключение.
     *
     * @return integer
     */
    private function getCodeByException($exception): int
    {
        if ($code = $exception->getCode()) {
            return $code;
        }
        if ($exception instanceof NotFoundHttpException) {
            return static::NOT_FOUND;
        }
        if ($exception instanceof BadRequestHttpException) {
            return \mb_strpos($exception->getMessage(), 'Не удалось проверить переданные данные') !== false
                ? static::INVALID_CSRF
                : static::INVALID_PARAMS;
        }
        if ($exception instanceof ForbiddenHttpException) {
            return static::FORBIDDEN;
        }

        return static::UNKNOWN;
    }

    /**
     * Генерация нового CSRF токена.
     */
    private function regenerateCsrfToken(): void
    {
        $request = Yii::$app->getRequest();
        if ($request->getIsPost() || $request->getIsPatch() || $request->getIsDelete()) {
            $request->getCsrfToken(true);
        }
    }
}
