<?php

namespace EmailDirectMarketingBundle\Tests\Exception;

use EmailDirectMarketingBundle\Exception\InvalidConfigurationException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(InvalidConfigurationException::class)]
final class InvalidConfigurationExceptionTest extends AbstractExceptionTestCase
{
    public function testExtendsRuntimeException(): void
    {
        $exception = new InvalidConfigurationException();

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testConstructorWithMessage(): void
    {
        $message = 'Invalid configuration error';
        $exception = new InvalidConfigurationException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testConstructorWithMessageAndCode(): void
    {
        $message = 'Invalid configuration error';
        $code = 400;
        $exception = new InvalidConfigurationException($message, $code);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }

    public function testConstructorWithPreviousException(): void
    {
        $previousException = new \InvalidArgumentException('Previous error');
        $exception = new InvalidConfigurationException('Invalid configuration error', 0, $previousException);

        $this->assertSame($previousException, $exception->getPrevious());
    }
}
