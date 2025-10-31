<?php

namespace EmailDirectMarketingBundle\Tests\Exception;

use EmailDirectMarketingBundle\Exception\ConfigurationException;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(ConfigurationException::class)]
final class ConfigurationExceptionTest extends AbstractExceptionTestCase
{
    public function testExtendsRuntimeException(): void
    {
        $exception = new ConfigurationException();

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testConstructorWithMessage(): void
    {
        $message = 'Configuration error';
        $exception = new ConfigurationException($message);

        $this->assertSame($message, $exception->getMessage());
    }

    public function testConstructorWithMessageAndCode(): void
    {
        $message = 'Configuration error';
        $code = 500;
        $exception = new ConfigurationException($message, $code);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
    }

    public function testConstructorWithPreviousException(): void
    {
        $previousException = new \InvalidArgumentException('Previous error');
        $exception = new ConfigurationException('Configuration error', 0, $previousException);

        $this->assertSame($previousException, $exception->getPrevious());
    }
}
