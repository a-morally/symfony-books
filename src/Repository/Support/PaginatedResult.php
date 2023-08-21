<?php

namespace App\Repository\Support;

/**
 * @template T
 */
class PaginatedResult
{
    /**
     * @param T[] $items
     * @param integer $totalAmount
     */
    public function __construct(private array $items, private int $totalAmount, private int $perPage)
    {
    }

    /**
     * @return T[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalAmount(): int
    {
        return $this->totalAmount;
    }

    public function getPagesAmount(): int
    {
        return ceil($this->totalAmount / $this->perPage);
    }
}
