<?php

namespace EmailDirectMarketingBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineEnhanceBundle\Repository\CommonRepositoryAware;
use EmailDirectMarketingBundle\Entity\Template;

/**
 * @method Template|null find($id, $lockMode = null, $lockVersion = null)
 * @method Template|null findOneBy(array $criteria, array $orderBy = null)
 * @method Template[] findAll()
 * @method Template[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TemplateRepository extends ServiceEntityRepository
{
    use CommonRepositoryAware;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Template::class);
    }
}
