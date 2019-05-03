<?php

namespace App\Repository;

use App\Entity\Verification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use App\Entity\Titles;
use Doctrine\ORM\Query;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @method Verification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Verification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Verification[]    findAll()
 * @method Verification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeasonRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Titles::class);
    }

    private function createPaginator(Query $query, int $page): Pagerfanta
    {
        $paginator = new Pagerfanta(new DoctrineORMAdapter($query));
        $paginator->setMaxPerPage(Post::NUM_ITEMS);
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
        $page = $this->sanitizeSearchQuery($page);
        if(($sort != 'ASC')&&($sort != 'DESC')) $sort = 'DESC';
        if(($sortBy != 'date')&&($sortBy != 'name')){
            $sortBy = 'startyear';
        } else {
            if($sortBy=="name") $sortBy = "primarytitle";
            if($sortBy=="date") $sortBy = "startyear";
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
        $qb = $queryBuilder
//            ->orderBy('p.creationdatetime', 'DESC')
//            ->orderBy('p.creationdatetime', $sort)
            ->orderBy('p.'.$sortBy, $sort)
            ->setMaxResults($limit);
//            ->getQuery()
//            ->setMaxResults(1)
//            ->getResult();

        return $this->createPaginator($qb->getQuery(), $page);
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
