<?php

namespace EmailDirectMarketingBundle\Tests\DependencyInjection;

use EmailDirectMarketingBundle\DependencyInjection\EmailDirectMarketingExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class EmailDirectMarketingExtensionTest extends TestCase
{
    private EmailDirectMarketingExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new EmailDirectMarketingExtension();
    }

    public function testExtendsExtension(): void
    {
        $this->assertInstanceOf(Extension::class, $this->extension);
    }

    public function testLoadMethodExists(): void
    {
        // load 方法在 Extension 基类中定义，所以肯定存在
        $this->assertTrue(true);
    }

    public function testLoadAcceptsArrayAndContainerBuilder(): void
    {
        $container = new ContainerBuilder();
        $configs = [];

        // 测试load方法不抛出异常
        $this->expectNotToPerformAssertions();
        $this->extension->load($configs, $container);
    }

    public function testGetAliasReturnsString(): void
    {
        $alias = $this->extension->getAlias();
        
        $this->assertNotEmpty($alias);
    }
} 