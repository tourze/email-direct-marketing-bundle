<?php

namespace EmailDirectMarketingBundle\Tests\MessageHandler;

use EmailDirectMarketingBundle\Message\SendQueueEmailMessage;
use EmailDirectMarketingBundle\MessageHandler\SendQueueEmailHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(SendQueueEmailHandler::class)]
#[RunTestsInSeparateProcesses]
final class SendQueueEmailHandlerTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
    }

    public function testConstructorCreatesInstance(): void
    {
        $reflection = new \ReflectionClass(SendQueueEmailHandler::class);
        $this->assertTrue($reflection->isInstantiable());
    }

    public function testHandlerIsInvokable(): void
    {
        $reflection = new \ReflectionClass(SendQueueEmailHandler::class);

        $this->assertTrue($reflection->hasMethod('__invoke'));
    }

    public function testInvokeMethodAcceptsCorrectMessageType(): void
    {
        $reflection = new \ReflectionClass(SendQueueEmailHandler::class);
        $invokeMethod = $reflection->getMethod('__invoke');
        $parameters = $invokeMethod->getParameters();

        $this->assertCount(1, $parameters);

        $firstParameter = $parameters[0];
        $parameterType = $firstParameter->getType();

        $this->assertNotNull($parameterType);
        $this->assertSame(SendQueueEmailMessage::class, $parameterType instanceof \ReflectionNamedType ? $parameterType->getName() : (string) $parameterType);
    }
}
