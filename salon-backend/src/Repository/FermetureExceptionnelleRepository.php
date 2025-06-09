<?php

namespace App\Repository;

use App\Entity\FermetureExceptionnelle;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FermetureExceptionnelle>
 */
class FermetureExceptionnelleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FermetureExceptionnelle::class);
    }

    //    /**
    //     * @return FermetureExceptionnelle[] Returns an array of FermetureExceptionnelle objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?FermetureExceptionnelle
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
