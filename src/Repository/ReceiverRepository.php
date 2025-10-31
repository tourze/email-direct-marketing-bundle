<?php

declare(strict_types=1);

namespace EmailDirectMarketingBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use EmailDirectMarketingBundle\Entity\Receiver;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<Receiver>
 */
#[AsRepository(entityClass: Receiver::class)]
class ReceiverRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Receiver::class);
    }

    /**
     * 查找所有未退订的有效收件人
     *
     * @return Receiver[]
     */
    public function findAllActiveReceivers(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.unsubscribed = false OR r.unsubscribed IS NULL')
            ->orderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 根据标签查找收件人
     *
     * @param array<string> $tags 标签列表
     *
     * @return Receiver[]
     */
    public function findByTags(array $tags): array
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.unsubscribed != true')
        ;

        if ([] !== $tags) {
            $conditions = [];
            foreach ($tags as $i => $tag) {
                $paramName = 'tag_' . $i;
                $conditions[] = $qb->expr()->like('r.tags', ':' . $paramName);
                $qb->setParameter($paramName, '%"' . $tag . '"%');
            }

            $qb->andWhere($qb->expr()->orX(...$conditions));
        }

        return $qb->orderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * 查找指定邮箱地址的收件人
     *
     * @param string $email 邮箱地址
     */
    public function findByEmail(string $email): ?Receiver
    {
        return $this->createQueryBuilder('r')
            ->where('r.emailAddress = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * 查找长时间未收到邮件的收件人
     *
     * @param \DateTimeInterface $beforeDate 指定日期之前
     *
     * @return Receiver[]
     */
    public function findNotContactedSince(\DateTimeInterface $beforeDate): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.unsubscribed = false OR r.unsubscribed IS NULL')
            ->andWhere('r.lastSendTime < :beforeDate OR r.lastSendTime IS NULL')
            ->setParameter('beforeDate', $beforeDate)
            ->orderBy('r.lastSendTime', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function save(Receiver $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Receiver $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
