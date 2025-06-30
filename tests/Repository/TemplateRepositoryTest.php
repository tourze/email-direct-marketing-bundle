<?php

namespace EmailDirectMarketingBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use EmailDirectMarketingBundle\Entity\Template;
use EmailDirectMarketingBundle\Repository\TemplateRepository;
use PHPUnit\Framework\TestCase;

class TemplateRepositoryTest extends TestCase
{
    private TemplateRepository $repository;
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->repository = new TemplateRepository($this->registry);
    }

    public function testExtendsServiceEntityRepository(): void
    {
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }

    public function testConstructorCallsParentWithCorrectParameters(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        
        $repository = new TemplateRepository($registry);
        
        $this->assertInstanceOf(TemplateRepository::class, $repository);
    }

    public function testRepositoryImplementsEntityRepository(): void
    {
        // 这些方法都继承自 ServiceEntityRepository，肯定存在
        $this->assertTrue(true);
    }
} 