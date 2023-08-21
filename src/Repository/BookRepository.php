<?php

namespace App\Repository;

use App\Entity\Book;
use App\Repository\Book\SearchParams;
use App\Repository\Support\PaginatedResult;
use App\Repository\Trait\HasPagination;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class BookRepository extends ServiceEntityRepository
{
    use HasPagination;

    public function __construct(ManagerRegistry $registry, private int $perPage)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * @return PaginatedResult<Book>
     */
    public function findAllBySearchParams(SearchParams $params, int $page = 0): PaginatedResult
    {
        $qb = $this->createQueryBuilder('t');

        if ($params->getCategory()) {
            $qb->join('t.categories', 'c');
            $qb->andWhere('c = :category')->setParameter('category', $params->getCategory());
        }

        if ($params->getTitle()) {
            $qb->andWhere('t.title like :title')->setParameter(
                'title',
                '%' . addcslashes($params->getTitle(), '%_') . '%'
            );
        }

        if ($params->getAuthor()) {
            $qb->join('t.authors', 'a');
            $qb->andWhere('a = :author')->setParameter('author', $params->getAuthor());
        } elseif ($params->getAuthorName()) {
            $qb->join('t.authors', 'a');
            $qb->andWhere('a.name like :authorName')->setParameter(
                'authorName',
                '%' . addcslashes($params->getAuthorName(), '%_') . '%'
            );
        }

        if ($params->getPublishmentStatus()) {
            $qb->andWhere('t.publishmentStatus = :publishmentStatus')->setParameter(
                'publishmentStatus',
                $params->getPublishmentStatus()
            );
        }

        return $this->paginate($qb->getQuery(), $page, $this->perPage);
    }

    /**
     * @return Book[]
     */
    public function findRelatedTo(Book $book): array
    {
        $categories = $book->getCategories();

        $qb = $this->createQueryBuilder('t');
        $qb->join('t.categories', 'c');
        $qb->where('c in (:categories) and t != :book');
        $qb->setParameters([
            'book' => $book,
            'categories' => $categories,
        ]);
        $qb->setMaxResults($this->perPage);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Book[] $entities
     * @return Book[];
     */
    public function findDuplicatesOf(array $entities): array
    {
        $uniquenessHashes = array_map(fn(Book $entity) => $entity->getUniquenessHash(), $entities);

        $qb = $this->createQueryBuilder('t');
        $qb->select()
            ->where('t.uniquenessHash in (:hashes)')
            ->setParameter('hashes', $uniquenessHashes);

        return $qb->getQuery()->getResult();
    }
}
