<?php

declare(strict_types=1);

namespace tests\unit;

use app\components\ErrorHandler;

/**
 * Class ErrorHandlerTest
 *
 * @package    tests\unit
 * @author     Nikolaj Rudakov <nnrudakov@gmail.com>
 * @copyright  2020 Nikolaj Rudakov
 */
class ErrorHandlerTest extends BaseUnit
{
    /**
     * @var ErrorHandler
     */
    private ErrorHandler $handler;

    protected function _before(): void
    {
        parent::_before();
        $this->handler = new ErrorHandler();
    }

    public function testConvertExceptionToArray(): void
    {
        $convertExceptionToArray = new \ReflectionMethod($this->handler, 'convertExceptionToArray');
        $convertExceptionToArray->setAccessible(true);
        self::assertIsArray($convertExceptionToArray->invoke($this->handler, new \Exception()));
    }

    public function testGetCodeByException(): void
    {
        $getCodeByException = new \ReflectionMethod($this->handler, 'getCodeByException');
        $getCodeByException->setAccessible(true);
        self::assertEquals(ErrorHandler::UNKNOWN, $getCodeByException->invoke($this->handler, new \Exception()));
        self::assertEquals(ErrorHandler::NOT_FOUND, $getCodeByException->invoke($this->handler, new \yii\web\NotFoundHttpException()));
        self::assertEquals(
            ErrorHandler::INVALID_CSRF,
            $getCodeByException->invoke(
                $this->handler,
                new \yii\web\BadRequestHttpException(\Yii::t('yii', 'Unable to verify your data submission.'))
            )
        );
        self::assertEquals(-1, $getCodeByException->invoke($this->handler, new \Exception('message', -1)));
    }
}
