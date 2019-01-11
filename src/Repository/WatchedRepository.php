<?php

namespace App\Repository;

use App\Entity\Watched;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Watched|null find($id, $lockMode = null, $lockVersion = null)
 * @method Watched|null findOneBy(array $criteria, array $orderBy = null)
 * @method Watched[]    findAll()
 * @method Watched[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WatchedRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Watched::class);
    }

//    /**
//     * @return Watched[] Returns an array of Watched objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Watched
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
