<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Post::class);
    }

    // /**
    //  * @return Posts[] Returns an array of Posts objects
    //  */
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
    public function findOneBySomeField($value): ?Posts
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findByHot24h($max = 10)
    {
        $now = new \DateTime;
        $now->modify( '-'.(date('j')-1).' day' );


        return $this->createQueryBuilder('p')
            ->andWhere("p.date > '".$now->format("Y-m-d H:i:s")."'")
            ->orderBy('p.points', 'DESC')
            ->setMaxResults($max)
            ->getQuery()
            ->getResult()
            ;

//        return $this->createQueryBuilder('od')
//            ->join('od.order', 'o')
//            ->addSelect('o')
//            ->where('o.userid = :userid')
//            ->andWhere('od.orderstatusid IN (:orderstatusid)')
//            ->setParameter('userid', $userid)
//            ->setParameter('orderstatusid', array(5, 6, 7, 8, 10))
//            ->getQuery()->getResult()
//        ;
    }
}
