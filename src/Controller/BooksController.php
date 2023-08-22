<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BooksController extends AppController
{
    public function __construct(private BookRepository $books)
    {
    }

    #[Route('/books/{id}', name: 'books_show')]
    public function show(int $id): Response
    {
        /** @var ?Book */
        $book = $this->books->find($id);
        if (!$book) {
            throw $this->createNotFoundException('Book not found');
        }

        $relatedBooks = $this->books->findRelatedTo($book);

        return $this->render('books/show.html.twig', [
            'book' => $book,
            'relatedBooks' => $relatedBooks,
        ]);
    }
}
