<?php

namespace EmailDirectMarketingBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineEnhanceBundle\Repository\CommonRepositoryAware;
use EmailDirectMarketingBundle\Entity\Queue;

/**
 * @method Queue|null find($id, $lockMode = null, $lockVersion = null)
 * @method Queue|null findOneBy(array $criteria, array $orderBy = null)
 * @method Queue[] findAll()
 * @method Queue[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QueueRepository extends ServiceEntityRepository
{
    use CommonRepositoryAware;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Queue::class);
    }
}
