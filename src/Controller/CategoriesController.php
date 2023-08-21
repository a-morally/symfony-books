<?php

namespace App\Controller;

use App\Entity\BookCategory;
use App\Repository\BookRepository;
use App\Repository\Book\SearchParams;
use App\Repository\BookCategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoriesController extends AbstractController
{
    public function __construct(private BookCategoryRepository $categories, private BookRepository $books)
    {
    }

    #[Route('/categories', name: 'categories')]
    public function list(): Response
    {
        /** @var BookCategory[] */
        $categories = $this->categories->findAll();

        return $this->render('categories/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/categories/{id}', name: 'categories_show')]
    public function show(Request $request, int $id): Response
    {
        /** @var ?BookCategory */
        $category = $this->categories->find($id);
        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        $page = (int) $request->get('page');
        $title = (string) trim($request->get('title'));
        $authorName = (string) trim($request->get('authorName'));
        $publishmentStatus = (string) trim($request->get('publishmentStatus'));

        $params = new SearchParams();
        $params->setCategory($category);
        if ($title) {
            $params->setTitle($title);
        }
        if ($authorName) {
            $params->setAuthorName($authorName);
        }
        if ($publishmentStatus) {
            $params->setPublishmentStatus($publishmentStatus);
        }

        $result = $this->books->findAllBySearchParams($params, $page);

        return $this->render('categories/show.html.twig', [
            'category' => $category,
            'books' => $result->getItems(),
            'pagesAmount' => $result->getPagesAmount(),
        ]);
    }
}
