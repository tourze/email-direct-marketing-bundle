<?php

namespace EmailDirectMarketingBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use EmailDirectMarketingBundle\Entity\Receiver;

/**
 * @method Receiver|null find($id, $lockMode = null, $lockVersion = null)
 * @method Receiver|null findOneBy(array $criteria, array $orderBy = null)
 * @method Receiver[] findAll()
 * @method Receiver[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
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
            ->getResult();
    }

    /**
     * 根据标签查找收件人
     *
     * @param array $tags 标签列表
     * @return Receiver[]
     */
    public function findByTags(array $tags): array
    {
        $tagConditions = [];
        $parameters = [];

        foreach ($tags as $i => $tag) {
            $paramName = 'tag_' . $i;
            $tagConditions[] = "JSON_SEARCH(r.tags, 'one', :{$paramName}) IS NOT NULL";
            $parameters[$paramName] = $tag;
        }

        $qb = $this->createQueryBuilder('r')
            ->where('r.unsubscribed != true');

        if (!empty($tagConditions)) {
            $qb->andWhere('(' . implode(' OR ', $tagConditions) . ')');
            
            foreach ($parameters as $key => $value) {
                $qb->setParameter($key, $value);
            }
        }

        return $qb->orderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * 查找指定邮箱地址的收件人
     *
     * @param string $email 邮箱地址
     * @return Receiver|null
     */
    public function findByEmail(string $email): ?Receiver
    {
        return $this->createQueryBuilder('r')
            ->where('r.emailAddress = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * 查找长时间未收到邮件的收件人
     *
     * @param \DateTimeInterface $beforeDate 指定日期之前
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
            ->getResult();
    }
}
