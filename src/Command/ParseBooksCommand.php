<?php

namespace App\Command;

use App\Entity\Book;
use Exception;
use App\Repository\BookRepository;
use App\Service\BookParser\BookParser;
use App\Repository\BookAuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\BookCategoryRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\BookParser\Exception\BookParserException;
use App\Service\BookParser\ParserResult;
use App\Service\Upload\Exception\UploadException;
use App\Service\Upload\FileUploader;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

#[AsCommand(name: 'app:books:load', description: 'Loads and parses books from file and stores them into database')]
class ParseBooksCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private BookParser $parser,
        private BookRepository $books,
        private BookAuthorRepository $authors,
        private BookCategoryRepository $categories,
        private FileUploader $fileUploader
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('filepath', InputArgument::REQUIRED, 'Path to the file from which to load');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Parsing books');

        $filepath = (string) $input->getArgument('filepath');

        try {
            $file = new File($filepath);
        } catch (FileNotFoundException $e) {
            $io->error($this->formatException($e));
            return Command::FAILURE;
        }

        try {
            $result = $this->parser->parse($file);
        } catch (BookParserException $e) {
            $io->error($this->formatException($e));
            return Command::FAILURE;
        }

        $parsedAmount = count($result->getBooks());
        $failedAmount = count($result->getFailed());

        $io->text('Books deduplication');

        $dupeBooks = $this->books->findDuplicatesOf($result->getBooks());
        $dupeAuthors = $this->authors->findDuplicatesOf($result->getAuthors());
        $dupeCategories = $this->categories->findDuplicatesOf($result->getCategories());
        $result = $this->parser->dedupe($result, $dupeBooks, $dupeAuthors, $dupeCategories);
        $books = $result->getBooks();

        $io->title('Preparing books');
        $io->progressStart(count($books));

        $defaultCategory = $this->categories->findDefault();
        $defaultCategoryHash = (string) $defaultCategory->getUniquenessHash();
        $result->setCategory($defaultCategoryHash, $defaultCategory);

        foreach ($books as $bookHash => $book) {
            if (count($result->getBookCategories($bookHash)) === 0) {
                $result->addBookCategories($bookHash, $defaultCategoryHash);
            }

            $book = $this->setBookAuthors($book, $bookHash, $result);
            $book = $this->setBookCategories($book, $bookHash, $result);
            $this->em->persist($book);

            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->text('Flushing new books to DB');

        try {
            $this->em->flush();
        } catch (Exception $e) {
            $io->error($this->formatException($e));
            return Command::FAILURE;
        }

        $io->title('Fetching books images');
        $io->progressStart(count($books));

        $position = 0;
        foreach ($books as $book) {
            $position++;
            $book = $this->fetchBookThumbnail($book);

            // Saving for redundancy
            if ($position % 10 === 0) {
                $this->em->flush();
            }

            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->text('Flushing remaining changes to DB');

        try {
            $this->em->flush();
        } catch (Exception $e) {
            $io->error($this->formatException($e));
            return Command::FAILURE;
        }

        $booksAmount = count($result->getBooks());
        $newBooksAmount = $booksAmount - count($dupeBooks);
        $authorsAmount = count($result->getAuthors());
        $newAuthorsAmount = $authorsAmount - count($dupeAuthors);
        $categoriesAmount = count($result->getCategories());
        $newCategoriesAmount = $categoriesAmount - count($dupeCategories);

        $io->success([
            "Parsed {$parsedAmount} (failed: {$failedAmount})",
            "Books: {$booksAmount} (new: {$newBooksAmount})",
            "Authors: {$authorsAmount} (new: {$newAuthorsAmount})",
            "Categories: {$categoriesAmount} (new: {$newCategoriesAmount})",
        ]);

        return Command::SUCCESS;
    }

    private function setBookAuthors(Book $book, string $bookHash, ParserResult $result): Book
    {
        $authorHashes = $result->getBookAuthors($bookHash);
        foreach ($authorHashes as $authorHash) {
            $author = $result->getAuthor($authorHash);
            if (!$author) {
                continue;
            }

            $book->addAuthor($author);
        }

        return $book;
    }

    private function setBookCategories(Book $book, string $bookHash, ParserResult $result): Book
    {
        $categoryHashes = $result->getBookCategories($bookHash);
        foreach ($categoryHashes as $categoryHash) {
            $category = $result->getCategory($categoryHash);
            if (!$category) {
                continue;
            }

            $book->addCategory($category);
        }

        return $book;
    }

    private function fetchBookThumbnail(Book $book): Book
    {
        if ($book->getThumbnailFilename()) {
            return $book;
        }

        $url = $book->getThumbnailUrl();
        if (!$url) {
            return $book;
        }
        try {
            $filename = $this->fileUploader->uploadFromUrl($url);
        } catch (UploadException $e) {
            return $book;
        }

        $book->setThumbnailFilename($filename);
        return $book;
    }

    private function formatException(Exception $e): string
    {
        $message = $e->getMessage();
        $class = $e::class;

        if ($message) {
            return "Parser failed with exception: \"{$class}\" and message \"{$message}\"";
        }

        return "Parser failed with exception \"{$class}\"";
    }
}
