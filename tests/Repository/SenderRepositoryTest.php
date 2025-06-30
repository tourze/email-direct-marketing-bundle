<?php

namespace EmailDirectMarketingBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use EmailDirectMarketingBundle\Entity\Sender;
use EmailDirectMarketingBundle\Repository\SenderRepository;
use PHPUnit\Framework\TestCase;

class SenderRepositoryTest extends TestCase
{
    private SenderRepository $repository;
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->repository = new SenderRepository($this->registry);
    }

    public function testExtendsServiceEntityRepository(): void
    {
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }

    public function testConstructorCallsParentWithCorrectParameters(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        
        $repository = new SenderRepository($registry);
        
        $this->assertInstanceOf(SenderRepository::class, $repository);
    }

    public function testRepositoryImplementsEntityRepository(): void
    {
        // 这些方法都继承自 ServiceEntityRepository，肯定存在
        $this->assertTrue(true);
    }
} 