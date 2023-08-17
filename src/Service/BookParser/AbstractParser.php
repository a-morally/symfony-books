<?php

namespace App\Service\BookParser;

use App\Entity\Book;
use App\Entity\BookAuthor;
use App\Entity\BookCategory;
use App\Service\BookParser\Exception\ParsingException;

abstract class AbstractParser
{
    /**
     * @param BookAuthor $authors
     * @param BookCategory $categories
     * @return ParserResult
     */
    protected function pack(ParserResult $result, Book $book, array $authors, array $categories): ParserResult
    {
        $bookHash = $book->getUniquenessHash();

        if (!$bookHash) {
            throw new ParsingException("Book's uniquenessHash does not exist");
        }

        $result->setBook($bookHash, $book);

        foreach ($authors as $author) {
            $hash = $author->getUniquenessHash();
            if (!$hash) {
                continue;
            }

            $result->setAuthor($hash, $author);
            $result->addBookAuthors($bookHash, $hash);
        }

        foreach ($categories as $category) {
            $hash = $category->getUniquenessHash();
            if (!$hash) {
                continue;
            }

            $result->setCategory($hash, $category);
            $result->addBookCategories($bookHash, $hash);
        }

        return $result;
    }
}
