<?php

namespace EmailDirectMarketingBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineEnhanceBundle\Repository\CommonRepositoryAware;
use EmailDirectMarketingBundle\Entity\Sender;

/**
 * @method Sender|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sender|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sender[] findAll()
 * @method Sender[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SenderRepository extends ServiceEntityRepository
{
    use CommonRepositoryAware;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sender::class);
    }
}
