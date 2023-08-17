<?php

namespace App\Service\BookParser;

use App\Entity\Book;
use App\Entity\BookAuthor;
use App\Entity\BookCategory;

class ParserResult
{
    /**
     * @var object[]
     */
    private array $failed = [];

    /**
     * Books mapped by hash
     * @var Book[]
     */
    private array $books = [];

    /**
     * Array of arrays where hash to get an array is book hash
     */
    private array $bookAuthors = [];

    /**
     * Array of arrays where hash to get an array is book hash
     */
    private array $bookCategories = [];

    /**
     * Authors mapped by hash
     * @var BookAuthor[]
     */
    private array $authors = [];

    /**
     * Categories mapped by hash
     * @var BookCategory[]
     */
    private array $categories = [];

    /**
     * @return object[]
     */
    public function getFailed(): array
    {
        return $this->failed;
    }

    public function addFailed(object $failed): static
    {
        $this->failed[] = $failed;
        return $this;
    }

    /**
     * @return Book[]
     */
    public function getBooks(): array
    {
        return $this->books;
    }

    public function getBook(string $hash): ?Book
    {
        return $this->books[$hash] ?? null;
    }

    public function setBook(string $hash, Book $book): static
    {
        $this->books[$hash] = $book;
        return $this;
    }

    /**
     * @return BookAuthor[]
     */
    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function getAuthor(string $hash): ?BookAuthor
    {
        return $this->authors[$hash] ?? null;
    }

    public function setAuthor(string $hash, BookAuthor $author): static
    {
        $this->authors[$hash] = $author;
        return $this;
    }

    /**
     * @return BookCategory[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getCategory(string $hash): ?BookCategory
    {
        return $this->categories[$hash] ?? null;
    }

    public function setCategory(string $hash, BookCategory $category): static
    {
        $this->categories[$hash] = $category;
        return $this;
    }

    /**
     * Returns array of author hashes
     * @return string[]
     */
    public function getBookAuthors(string $bookHash): array
    {
        return $this->bookAuthors[$bookHash] ?? [];
    }

    public function addBookAuthors(string $bookHash, string ...$authorHashes): static
    {
        $authors = $this->getBookAuthors($bookHash);

        foreach ($authorHashes as $hash) {
            if (in_array($hash, $authors)) {
                continue;
            }
            $authors[] = $hash;
        }
        $this->bookAuthors[$bookHash] = $authors;

        return $this;
    }

    /**
     * Returns array of category hashes
     * @return string[]
     */
    public function getBookCategories(string $bookHash): array
    {
        return $this->bookCategories[$bookHash] ?? [];
    }

    public function addBookCategories(string $bookHash, string ...$categoryHashes): static
    {
        $categories = $this->getBookCategories($bookHash);

        foreach ($categoryHashes as $hash) {
            if (in_array($hash, $categories)) {
                continue;
            }
            $categories[] = $hash;
        }
        $this->bookCategories[$bookHash] = $categories;

        return $this;
    }
}
