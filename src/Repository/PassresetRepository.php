<?php

namespace App\Repository;

use App\Entity\Passreset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Passreset|null find($id, $lockMode = null, $lockVersion = null)
 * @method Passreset|null findOneBy(array $criteria, array $orderBy = null)
 * @method Passreset[]    findAll()
 * @method Passreset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PassresetRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Passreset::class);
    }

//    /**
//     * @return Passreset[] Returns an array of Passreset objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Passreset
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */



    public function findOneByUserid($value): ?Passreset
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.userid = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }


    public function findOneByCode($value): ?Passreset
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.code = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

}
