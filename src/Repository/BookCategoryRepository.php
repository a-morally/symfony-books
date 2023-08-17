<?php

namespace App\Repository;

use App\Entity\BookCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class BookCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private string $defaultTitle)
    {
        parent::__construct($registry, BookCategory::class);
    }

    public function findDefault(): BookCategory
    {
        $category = $this->findBy(['isDefault' => true])[0] ?? null;

        // Create default category if non-existent
        if (!$category) {
            $em = $this->getEntityManager();
            $category = new BookCategory();
            $category->setTitle($this->defaultTitle);
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
