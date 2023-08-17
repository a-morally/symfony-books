<?php

namespace App\Service\BookParser;

use App\Entity\Book;
use App\Entity\BookAuthor;
use App\Entity\BookCategory;

class ParsedEntry
{
    /**
     * @param BookAuthor[] $authors
     * @param BookCategory[] $categories
     */
    public function __construct(private Book $book, private array $authors, private array $categories)
    {
    }

    public function getBook(): Book
    {
        return $this->book;
    }

    /**
     * @return BookAuthor[]
     */
    public function getAuthors(): array
    {
        return $this->authors;
    }

    /**
     * @return BookCategory[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }
}
