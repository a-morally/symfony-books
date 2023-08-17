<?php

namespace App\Repository;

use App\Entity\BookAuthor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BookAuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BookAuthor::class);
    }

    /**
     * @param BookAuthor[] $entities
     * @return BookAuthor[];
     */
    public function findDuplicatesOf(array $entities): array
    {
        $uniquenessHashes = array_map(fn(BookAuthor $entity) => $entity->getUniquenessHash(), $entities);

        $qb = $this->createQueryBuilder('t');
        $qb->select()
            ->where('t.uniquenessHash in (:hashes)')
            ->setParameter('hashes', $uniquenessHashes);

        return $qb->getQuery()->getResult();
    }
}
