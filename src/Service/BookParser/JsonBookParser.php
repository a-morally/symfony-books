<?php

namespace App\Service\BookParser;

use App\Entity\Book;
use DateTimeImmutable;
use App\Entity\BookAuthor;
use App\Entity\BookCategory;
use Symfony\Component\HttpFoundation\File\File;
use App\Service\BookParser\Exception\ParsingException;

/**
 * Parses books and categories from json file
 */
class JsonBookParser extends AbstractParser implements ParserInterface
{
    /**
     * @throws ParsingException
     */
    public function parse(File $file): ParserResult
    {
        $result = new ParserResult();

        // TODO find a way to iteratively decode json
        // since file may be too big to fit in memory
        $content = $file->getContent();
        $entries = json_decode($content);

        if ($entries === null) {
            throw new ParsingException('Could not parse json contents');
        }

        if (!is_array($entries) || !is_object($entries[0] ?? null)) {
            throw new ParsingException('Parsed data must be an array of objects');
        }

        foreach ($entries as $entry) {
            $book = $this->parseBook($entry);
            if (!$book) {
                $result->addFailed($entry);
                continue;
            }

            $authors = $this->parseAuthors($entry);
            $categories = $this->parseCategories($entry);

            $result = $this->pack($result, $book, $authors, $categories);
        }

        return $result;
    }

    private function parseBook(object $entry): ?Book
    {
        $title = trim((string) ($entry->title ?? ''));
        $isbn = trim((string) ($entry->isbn ?? ''));
        $pageCount = (int) ($entry->pageCount ?? 0);
        $pubDate = $entry->publishedDate->{'$date'} ?? null;
        $thumbUrl = trim((string) ($entry->thumbnailUrl ?? ''));
        $descShort = trim((string) ($entry->shortDescription ?? ''));
        $descLong = trim((string) ($entry->longDescription ?? ''));
        $pubStatus = strtolower(trim((string) ($entry->status ?? '')));

        if ($pubDate) {
            $pubDate = new DateTimeImmutable($pubDate);
            $pubDate = $pubDate === false ? null : $pubDate;
        }

        if (!$isbn || !$title || !in_array($pubStatus, Book::STATUSES)) {
            return null;
        }

        $book = new Book();
        $book->setTitle($title);
        $book->setIsbn($isbn);
        $book->setPageCount($pageCount);
        $book->setThumbnailUrl($thumbUrl);
        $book->setThumbnailFilename("");
        $book->setShortDescription($descShort);
        $book->setLongDescription($descLong);
        $book->setPublishmentStatus($pubStatus);

        if (isset($pubDate)) {
            $book->setPublishedAt($pubDate);
        }

        return $book;
    }

    /**
     * @return BookAuthor[]
     */
    private function parseAuthors(object $entry): array
    {
        $names = (array) $entry->authors ?? [];
        $authors = [];

        foreach ($names as $name) {
            if (!is_string($name)) {
                continue;
            }

            $name = trim($name);
            $name = $this->normalizeName($name);
            if (!$name) {
                continue;
            }

            $author = new BookAuthor();
            $author->setName($name);
            $authors[] = $author;
        }

        return $authors;
    }

    /**
     * @return BookCategory[]
     */
    private function parseCategories(object $entry): array
    {
        $titles = (array) $entry->categories ?? [];
        $categories = [];

        foreach ($titles as $title) {
            if (!is_string($title)) {
                continue;
            }

            $title = trim($title);
            $title = $this->normalizeName($title);
            if (!$title) {
                continue;
            }

            $category = new BookCategory();
            $category->setTitle($title);
            $category->setIsDefault(false);
            $categories[] = $category;
        }

        return $categories;
    }

    private function normalizeName(string $input): string
    {
        return mb_strtoupper(mb_substr($input, 0, 1)) . mb_substr($input, 1);
    }
}
