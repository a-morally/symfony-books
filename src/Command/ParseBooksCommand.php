<?php

namespace App\Command;

use Exception;
use App\Repository\BookRepository;
use App\Service\BookParser\BookParser;
use App\Repository\BookAuthorRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\BookCategoryRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\BookParser\Exception\BookParserException;

#[AsCommand(name: 'app:books:load', description: 'Loads and parses books from file and stores them into database')]
class ParseBooksCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private BookParser $parser,
        private BookRepository $books,
        private BookAuthorRepository $authors,
        private BookCategoryRepository $categories
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

        $filepath = (string) $input->getArgument('filepath');

        $io->section('Parsing books');

        try {
            $result = $this->parser->parse($filepath);
        } catch (BookParserException $e) {
            $io->error($this->formatException($e));
            return Command::FAILURE;
        }
        $parsedAmount = count($result->getBooks());
        $failedAmount = count($result->getFailed());

        $dupeBooks = $this->books->findDuplicatesOf($result->getBooks());
        $dupeAuthors = $this->authors->findDuplicatesOf($result->getAuthors());
        $dupeCategories = $this->categories->findDuplicatesOf($result->getCategories());

        $result = $this->parser->dedupe($result, $dupeBooks, $dupeAuthors, $dupeCategories);

        $books = $result->getBooks();
        foreach ($books as $bookHash => $book) {
            $authorHashes = $result->getBookAuthors($bookHash);
            foreach ($authorHashes as $authorHash) {
                $author = $result->getAuthor($authorHash);
                if (!$author) {
                    continue;
                }

                $book->addAuthor($author);
            }

            $categoryHashes = $result->getBookCategories($bookHash);
            foreach ($categoryHashes as $categoryHash) {
                $category = $result->getCategory($categoryHash);
                if (!$category) {
                    continue;
                }

                $book->addCategory($category);
            }

            $this->em->persist($book);
        }

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
            "Parsed: {$parsedAmount} | Failed: {$failedAmount}",
            "Books: {$booksAmount} | New: {$newBooksAmount}",
            "Authors: {$authorsAmount} | New: {$newAuthorsAmount}",
            "Categories: {$categoriesAmount} | New: {$newCategoriesAmount}",
        ]);

        return Command::SUCCESS;
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
