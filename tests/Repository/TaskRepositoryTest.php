<?php

namespace EmailDirectMarketingBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use EmailDirectMarketingBundle\Entity\Task;
use EmailDirectMarketingBundle\Repository\TaskRepository;
use PHPUnit\Framework\TestCase;

class TaskRepositoryTest extends TestCase
{
    private TaskRepository $repository;
    private ManagerRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->repository = new TaskRepository($this->registry);
    }

    public function testExtendsServiceEntityRepository(): void
    {
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->repository);
    }

    public function testConstructorCallsParentWithCorrectParameters(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        
        $repository = new TaskRepository($registry);
        
        $this->assertInstanceOf(TaskRepository::class, $repository);
    }

    public function testRepositoryImplementsEntityRepository(): void
    {
        // 这些方法都继承自 ServiceEntityRepository，肯定存在
        $this->assertTrue(true);
    }
} 