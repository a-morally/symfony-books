<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
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
