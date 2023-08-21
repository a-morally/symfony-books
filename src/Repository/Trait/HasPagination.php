<?php

namespace App\Repository\Trait;

use App\Repository\Support\PaginatedResult;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;

trait HasPagination
{
    public function paginate(Query $dql, int $page, int $perPage): PaginatedResult
    {
        $paginator = new Paginator($dql);

        $paginator
            ->getQuery()
            ->setFirstResult($perPage * $page)
            ->setMaxResults($perPage);

        return new PaginatedResult($paginator->getQuery()->getResult(), $paginator->count(), $perPage);
    }
}
