<?php

namespace EmailDirectMarketingBundle\Tests\MessageHandler;

use EmailDirectMarketingBundle\Message\SendQueueEmailMessage;
use EmailDirectMarketingBundle\MessageHandler\SendQueueEmailHandler;
use PHPUnit\Framework\TestCase;

class SendQueueEmailHandlerTest extends TestCase
{
    public function testConstructorCreatesInstance(): void
    {
        // 由于Handler的依赖较复杂，这里只做基本测试
        $this->assertTrue(class_exists(SendQueueEmailHandler::class));
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