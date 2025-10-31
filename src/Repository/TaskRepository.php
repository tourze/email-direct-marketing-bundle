<?php

declare(strict_types=1);

namespace EmailDirectMarketingBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use EmailDirectMarketingBundle\Entity\Task;
use EmailDirectMarketingBundle\Enum\TaskStatus;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<Task>
 */
#[AsRepository(entityClass: Task::class)]
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * 查找所有有效的等待发送的任务
     *
     * @return Task[]
     */
    public function findAllWaitingTasks(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.valid = true')
            ->andWhere('t.status = :status')
            ->setParameter('status', TaskStatus::WAITING)
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找所有正在发送中的任务
     *
     * @return Task[]
     */
    public function findAllSendingTasks(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.valid = true')
            ->andWhere('t.status = :status')
            ->setParameter('status', TaskStatus::SENDING)
            ->orderBy('t.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据标签查询任务
     *
     * @param array<string> $tags 标签列表
     *
     * @return Task[]
     */
    public function findByTags(array $tags): array
    {
        $qb = $this->createQueryBuilder('t');
        $expr = $qb->expr();

        $conditions = [];
        foreach ($tags as $index => $tag) {
            $paramName = 'tag_' . $index;
            $conditions[] = $expr->like('t.tags', $expr->literal('%' . $tag . '%'));
        }

        if ([] !== $conditions) {
            $qb->where($expr->orX(...$conditions));
        }

        return $qb->orderBy('t.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(Task $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Task $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
