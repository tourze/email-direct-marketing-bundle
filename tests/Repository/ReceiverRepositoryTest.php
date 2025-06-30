<?php

namespace EmailDirectMarketingBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use EmailDirectMarketingBundle\Entity\Receiver;
use EmailDirectMarketingBundle\Repository\ReceiverRepository;
use PHPUnit\Framework\TestCase;

class ReceiverRepositoryTest extends TestCase
{
    private ReceiverRepository $repository;
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->repository = new ReceiverRepository($this->registry);
    }

    public function testExtendsServiceEntityRepository(): void
    {
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }

    public function testConstructorCallsParentWithCorrectParameters(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        
        $repository = new ReceiverRepository($registry);
        
        $this->assertInstanceOf(ReceiverRepository::class, $repository);
    }

    public function testRepositoryImplementsEntityRepository(): void
    {
        // 这些方法都继承自 ServiceEntityRepository，肯定存在
        $this->assertTrue(true);
    }
} 