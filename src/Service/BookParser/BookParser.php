<?php

namespace App\Service\BookParser;

use App\Entity\Book;
use App\Entity\BookAuthor;
use App\Entity\BookCategory;
use Symfony\Component\HttpFoundation\File\File;
use App\Service\BookParser\Exception\ParsingException;
use App\Service\BookParser\Exception\FileNotFoundException;
use App\Service\BookParser\Exception\UnsupportedFileTypeException;

class BookParser implements ParserInterface
{
    public const SUPPORTED_EXTENSIONS = ['json'];

    public function __construct(private JsonBookParser $jsonParser)
    {
    }

    /**
     * @throws FileNotFoundException
     * @throws UnsupportedFileTypeException
     * @throws ParsingException
     */
    public function parse(File $file): ParserResult
    {
        $extension = $file->getExtension();

        if (!$extension || !in_array($extension, self::SUPPORTED_EXTENSIONS)) {
            throw new UnsupportedFileTypeException();
        }

        try {
            $result = match ($extension) {
                'json' => $this->jsonParser->parse($file),
            };
        } catch (ParsingException $e) {
            throw $e;
        }

        return $result;
    }

    /**
     * @param Book[] $dupeBooks
     * @param BookAuthor[] $dupeAuthors
     * @param BookCategory[] $dupeCategories
     */
    public function dedupe(
        ParserResult $result,
        array $dupeBooks,
        array $dupeAuthors,
        array $dupeCategories
    ): ParserResult {
        foreach ($dupeBooks as $book) {
            $hash = $book->getUniquenessHash();
            if (!$hash) {
                continue;
            }

            $result->setBook($hash, $book);
        }

        foreach ($dupeAuthors as $author) {
            $hash = $author->getUniquenessHash();
            if (!$hash) {
                continue;
            }

            $result->setAuthor($hash, $author);
        }

        foreach ($dupeCategories as $category) {
            $hash = $category->getUniquenessHash();
            if (!$hash) {
                continue;
            }

            $result->setCategory($hash, $category);
        }

        return $result;
    }
}
