<?php

namespace App\Repository;

use App\Entity\Verification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\Titles;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @method Verification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Verification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Verification[]    findAll()
 * @method Verification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TitlesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Titles::class);
    }

    public function findLatest(int $page = 1): Pagerfanta
    {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.startyear', 'DESC');
        return $this->createPaginator($qb->getQuery(), $page);
    }

    private function createPaginator(Query $query, int $page): Pagerfanta
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($query));
        $paginator->setMaxPerPage(12);
        $paginator->setCurrentPage($page);
        return $paginator;
    }

    /**
     * @return Titles[]
     */
    public function findBySearchQuery(string $rawQuery, int $limit = 5, string $sort = "DESC", string $sortBy = "date", int $page = 1): array
    {
        $query = $this->sanitizeSearchQuery($rawQuery);
        $sort = $this->sanitizeSearchQuery($sort);
        $limit = $this->sanitizeSearchQuery($limit);
        $page = $this->sanitizeSearchQuery($page);
        if(($sort != 'ASC')&&($sort != 'DESC')) $sort = 'DESC';
        if(($sortBy != 'date')&&($sortBy != 'name')){
            $sortBy = 'creationdatetime';
        } else {
            if($sortBy=="name") $sortBy = "primarytitle";
            if($sortBy=="date") $sortBy = "endyear";
        }
        $searchTerms = $this->extractSearchTerms($query);
        if (0 === \count($searchTerms)) {
            return [];
        }
        $queryBuilder = $this->createQueryBuilder('p');
        foreach ($searchTerms as $key => $term) {
            $queryBuilder
                ->orWhere('p.primarytitle LIKE :t_'.$key)
                ->setParameter('t_'.$key, '%'.$term.'%')
            ;
        }
        return $queryBuilder
            ->orderBy('p.'.$sortBy, $sort)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Titles[]
     */
    public function findBySearchQueryPagin(string $rawQuery, int $limit = 5, string $sort = "DESC", string $sortBy = "relevance", int $page = 1): pagerfanta
    {
        $query = $this->sanitizeSearchQuery($rawQuery);
        $sort = $this->sanitizeSearchQuery($sort);
        $sortBy = $this->sanitizeSearchQuery($sortBy);
        $limit = $this->sanitizeSearchQuery($limit);
        $page = $this->sanitizeSearchQuery($page);
        if(($sort != 'ASC')&&($sort != 'DESC')) $sort = 'DESC';
        if(($sortBy != 'date')&&($sortBy != 'name')&&($sortBy != 'relevance')){
            $sortBy = null;
        } else {
            if($sortBy=="name") $sortBy = "primarytitle";
            if($sortBy=="date") $sortBy = "startyear";
            if($sortBy=="relevance") $sortBy = "relevance";
        }
        $searchTerms = $this->extractSearchTerms($query);
        if (0 === \count($searchTerms)) {
            return [];
        }
        $queryBuilder = $this->createQueryBuilder('p');
        foreach ($searchTerms as $key => $term) {
            $queryBuilder
//                ->Where("MATCH_AGAINST (p.primarytitle, :t_".$key." 'IN BOOLEAN MODE') > 0.0")
                ->andWhere("MATCH_AGAINST (p.primarytitle, :searchTerms) > 1")
                ->setParameter('searchTerms', $term)
//                ->Where("MATCH_AGAINST ($term, 'IN BOOLEAN MODE') > 0.0")
//                ->setParameter('t_'.$key, $term);
//                ->orWhere('p.primarytitle LIKE :t_'.$key)
//                ->setParameter('t_'.$key, '%'.$term.'%')
//                ->orWhere('MATCH(p.primarytitle) AGAINST (:t_'.$key." IN BOOLEAN MODE )")
//                ->setParameter('t_'.$key, $term)
//                ->setParameter('t_'.$key, '%'.$term.'%')
            ;
        }

           if(!$sortBy == "relevance") {
               $queryBuilder
                   ->orderBy('p.' . $sortBy, $sort);
           }
           echo "<script>alert(".var_dump($sortBy).")</script>";
//            ->setMaxResults($limit);
//            ->getQuery()
//            ->getResult();

        return $this->createPaginator($queryBuilder->getQuery(), $page);
    }

    /**
     * @return Titles[]
     */
    public function findBySearchQueryPagin2(string $rawQuery, int $limit = 999, string $sort = "DESC", string $sortBy = "relevance", int $page = 1, array $best_tconst): pagerfanta
    {
        $query = $this->sanitizeSearchQuery($rawQuery);
        $sort = $this->sanitizeSearchQuery($sort);
        $sortBy = $this->sanitizeSearchQuery($sortBy);
        $limit = $this->sanitizeSearchQuery($limit);
        $page = $this->sanitizeSearchQuery($page);
        if(($sort != 'ASC')&&($sort != 'DESC')) $sort = 'DESC';
        if(($sortBy != 'date')&&($sortBy != 'name')&&($sortBy != 'relevance')){
            $sortBy = null;
        } else {
            if($sortBy=="name") $sortBy = "primarytitle";
            if($sortBy=="date") $sortBy = "startyear";
            if($sortBy=="relevance") $sortBy = "relevance";
        }
//        $searchTerms = $this->extractSearchTerms($query);

        $queryBuilder = $this->createQueryBuilder('p')
//            ->addSelect("MATCH_AGAINST (p.primarytitle, :searchTerms 'IN NATURAL MODE') as score")
//            ->andWhere("MATCH_AGAINST (p.primarytitle, :searchTerms) > 1")
            ->Where("p.primarytitle LIKE :searchTerms")
            ->setParameter('searchTerms', '%'.$query.'%');

        foreach ($best_tconst as $best_tconst_one) {
            $queryBuilder
                ->andWhere("p.tconst != :best_tconst")
                ->setParameter('best_tconst', $best_tconst_one);
        }
//            ->setParameter('searchTerms', $query)
            $queryBuilder
                ->setMaxResults($limit);
//        var_dump($query);

//            var_dump($query);
//        die;
//                ->andWhere("MATCH_AGAINST (p.primarytitle, :searchTerms) > 1")
//                ->setParameter('searchTerms', $searchTerms);
//                ->Where("MATCH_AGAINST ($term, 'IN BOOLEAN MODE') > 0.0")
//                ->setParameter('t_'.$key, $term);
//                ->orWhere('p.primarytitle LIKE :t_'.$key)
//                ->setParameter('t_'.$key, '%'.$term.'%')
//                ->orWhere('MATCH(p.primarytitle) AGAINST (:t_'.$key." IN BOOLEAN MODE )")
//                ->setParameter('t_'.$key, $term)
//                ->setParameter('t_'.$key, '%'.$term.'%')


//        echo "<script>alert()</script>";

           if($sortBy !== "relevance"){
               $queryBuilder
                   ->orderBy('p.' . $sortBy, $sort);
//                   ->orderBy('score', $sort);
           }
//           echo "<script>alert(".var_dump($sortBy).")</script>";
//            ->setMaxResults($limit);
//            ->getQuery()
//            ->getResult();

        return $this->createPaginator($queryBuilder->getQuery(), $page);
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

