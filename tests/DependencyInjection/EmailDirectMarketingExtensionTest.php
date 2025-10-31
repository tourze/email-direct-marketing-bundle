<?php

namespace EmailDirectMarketingBundle\Tests\DependencyInjection;

use EmailDirectMarketingBundle\DependencyInjection\EmailDirectMarketingExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(EmailDirectMarketingExtension::class)]
final class EmailDirectMarketingExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    public function testConfigDirectoryExists(): void
    {
        $extension = new EmailDirectMarketingExtension();
        $reflection = new \ReflectionClass($extension);
        $method = $reflection->getMethod('getConfigDir');
        $method->setAccessible(true);
        $configDir = $method->invoke($extension);

        $this->assertStringEndsWith('/Resources/config', $configDir);
        $this->assertDirectoryExists($configDir);
    }

    public function testLoadAcceptsArrayAndContainerBuilder(): void
    {
        $extension = new EmailDirectMarketingExtension();
        $container = new ContainerBuilder();
        $container->setParameter('kernel.environment', 'test');
        $configs = [];

        // 测试load方法不抛出异常
        $this->expectNotToPerformAssertions();
        $extension->load($configs, $container);
    }

    public function testGetAliasReturnsString(): void
    {
        $extension = new EmailDirectMarketingExtension();
        $alias = $extension->getAlias();

        $this->assertNotEmpty($alias);
    }
}
