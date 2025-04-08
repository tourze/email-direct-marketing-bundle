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
}
