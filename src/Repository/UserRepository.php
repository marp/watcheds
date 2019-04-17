<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\Post;
use App\Entity\Tag;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

use FOS\ElasticaBundle\Repository;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneByEmail($email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $email)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findByUsername($title)
    {
        return $this->getEntityManager()
            ->createQuery(
//                'SELECT username, roles FROM AppBundle:User users WHERE users.username LIKE %:title%'
//                'SELECT username, roles FROM AppBundle:User users WHERE users.username COLLATE UTF8_GENERAL_CI LIKE %'.$title.'%'
                "SELECT username, roles FROM AppBundle:User users WHERE users.username LIKE $title"
            )
            //->setParameter('title', '%' . $title . '%')
            //->getQuery()
            ->getResult();
    }


    /**
     * @param $title
     * @return User[]
     */
    public function findByUsernameCustom($title){
        {
            return $this->getEntityManager()
                ->createQuery(
                'SELECT username, roles FROM AppBundle:User users WHERE users.username LIKE %:title%'
//                    'SELECT username, roles FROM user WHERE user.username COLLATE UTF8_GENERAL_CI LIKE %'.$title.'%'
                )
                //->setParameter('title', '%' . $title . '%')
                ->getResult();
        }
    }
//    /**
//     * @return User[] Returns an array of User objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @return User[]
     */
    public function findBySearchQuery(string $rawQuery, int $limit = 5, string $sort = "DESC", string $sortBy = "date"): array
    {
        $query = $this->sanitizeSearchQuery($rawQuery);
        $sort = $this->sanitizeSearchQuery($sort);
        if(($sort != 'ASC')&&($sort != 'DESC')) $sort = 'DESC';
        if(($sortBy != 'date')&&($sortBy != 'name')){
            $sortBy = 'creationdatetime';
        } else {
            if($sortBy=="name") $sortBy = "username";
            if($sortBy=="date") $sortBy = "creationdatetime";
        }
        $searchTerms = $this->extractSearchTerms($query);
        if (0 === \count($searchTerms)) {
            return [];
        }
        $queryBuilder = $this->createQueryBuilder('p');
        foreach ($searchTerms as $key => $term) {
            $queryBuilder
                ->orWhere('p.username LIKE :t_'.$key)
                ->setParameter('t_'.$key, '%'.$term.'%')
            ;
        }
        return $queryBuilder
//            ->orderBy('p.creationdatetime', 'DESC')
//            ->orderBy('p.creationdatetime', $sort)
            ->orderBy('p.'.$sortBy, $sort)
            ->setMaxResults($limit)
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
    /**
     * Splits the search query into terms and removes the ones which are irrelevant.
     */
    private function extractSearchTerms(string $searchQuery): array
    {
        $terms = array_unique(explode(' ', $searchQuery));
        return array_filter($terms, function ($term) {
            return 2 <= mb_strlen($term);
        });
    }
}
