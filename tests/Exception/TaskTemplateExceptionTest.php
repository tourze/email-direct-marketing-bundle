<?php

namespace EmailDirectMarketingBundle\Tests\Exception;

use EmailDirectMarketingBundle\Exception\TaskTemplateException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(TaskTemplateException::class)]
final class TaskTemplateExceptionTest extends AbstractExceptionTestCase
{
    public function testExtendsRuntimeException(): void
    {
        $exception = new TaskTemplateException();
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testConstructorWithMessage(): void
    {
        $message = 'Test message';
        $exception = new TaskTemplateException($message);
        $this->assertSame($message, $exception->getMessage());
    }

    public function testConstructorWithMessageAndCode(): void
    {
        $message = 'Test message';
        $code = 123;
        $exception = new TaskTemplateException($message, $code);
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }

    public function testConstructorWithPreviousException(): void
    {
        $previous = new \RuntimeException('Previous exception');
        $exception = new TaskTemplateException('Test message', 0, $previous);
        $this->assertSame($previous, $exception->getPrevious());
    }
}
