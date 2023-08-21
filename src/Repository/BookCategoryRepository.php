<?php

namespace App\Repository;

use App\Entity\BookCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BookCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private string $defaultName)
    {
        parent::__construct($registry, BookCategory::class);
    }

    /**
     * @return BookCategory[]
     */
    public function findAll(): array {
        $qb = $this->createQueryBuilder('t');
        $qb->orderBy("t.title", 'asc');

        return $qb->getQuery()->getResult();
    }

    public function findDefault(): BookCategory
    {
        $category = $this->findBy(['isDefault' => true])[0] ?? null;

        // Create default category if none exists
        if (!$category) {
            $em = $this->getEntityManager();
            $category = new BookCategory();
            $category->setName($this->defaultName);
            $category->setIsDefault(true);

            $em->persist($category);
            $em->flush();
        }

        return $category;
    }

    /**
     * @param BookCategory[] $entities
     * @return BookCategory[];
     */
    public function findDuplicatesOf(array $entities): array
    {
        $uniquenessHashes = array_map(fn(BookCategory $entity) => $entity->getUniquenessHash(), $entities);

        $qb = $this->createQueryBuilder('t');
        $qb->select()
            ->where('t.uniquenessHash in (:hashes)')
            ->setParameter('hashes', $uniquenessHashes);

        return $qb->getQuery()->getResult();
    }
}
