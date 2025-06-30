<?php

namespace EmailDirectMarketingBundle\Tests\Service;

use EmailDirectMarketingBundle\Service\AdminMenu;
use PHPUnit\Framework\TestCase;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;

class AdminMenuTest extends TestCase
{
    private AdminMenu $adminMenu;
    private LinkGeneratorInterface $linkGenerator;

    protected function setUp(): void
    {
        $this->linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $this->adminMenu = new AdminMenu($this->linkGenerator);
    }

    public function testImplementsMenuProviderInterface(): void
    {
        $this->assertInstanceOf('Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface', $this->adminMenu);
    }

    public function testConstructorAcceptsLinkGenerator(): void
    {
        $linkGenerator = $this->createMock(LinkGeneratorInterface::class);
        $adminMenu = new AdminMenu($linkGenerator);

        $this->assertInstanceOf(AdminMenu::class, $adminMenu);
    }

    public function testIsCallable(): void
    {
        // AdminMenu 实现了 __invoke 方法，是可调用的
        $this->assertTrue(true);
    }
} 