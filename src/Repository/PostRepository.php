<?php

namespace App\Repository;

use App\Entity\Post;
use App\Entity\Points;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\Query\Expr\GroupBy;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

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
            ->innerJoin('p.user', 'c')
            ->innerJoin('p.points', 'pp')
            ->leftJoin('p.comments', 'cc')
            ->andWhere("p.date > '".$now->format("Y-m-d H:i:s")."'")
            ->setMaxResults($max)
            ->groupBy('pp.post')
            ->orderBy('pp.post','DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByTag($max, string $tag)
    {
        $tag = $this->sanitizeSearchQuery($tag);
        $tag = '#'.$tag;
        return  $this->createQueryBuilder('p')
//            ->innerJoin('p.user', 'c')
//            ->leftJoin('p.points', 'pp')
//            ->leftJoin('p.comments', 'cc')
            ->Where('p.content LIKE :tag')
            ->setParameter('tag','%'.$tag.'%')
            ->setMaxResults($max)
            ->orderBy('p.date','DESC')
            ->getQuery()
            ->getResult();

    }

    /**
     * Removes all non-alphanumeric characters except whitespaces.
     */
    private function sanitizeSearchQuery(string $query): string
    {
        return trim(preg_replace('/[[:space:]]+/', ' ', $query));
    }
}
