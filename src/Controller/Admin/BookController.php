<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/book')]
class BookController extends AbstractController
{
    #[Route('', name: 'app_admin_book_index', methods: ['GET'])]
    public function index(Request $request, BookRepository $repository): Response
    {
        //$books = $repository->findAll();
        $page = $request->query->getInt('page', 1);
        $limit = 2;
        $books = $repository->paginateBook($page, $limit);
        $maxPage = ceil($books->getTotalItemCount() / $limit);
        //dd($books->count());

        return $this->render('admin/book/index.html.twig', [
            'books' => $books,
            'maxPage' => $maxPage,
            'page' => $page,
        ]);
    }


    #[Route('/list', name: 'app_admin_book_list', methods: ['GET'])]
    public function list(Request $request, BookRepository $repository): Response
    {
        //$books = $repository->findAll();
        $page = $request->query->getInt('page', 1);
        $limit = 4;
        $books = $repository->paginateBook($page, $limit);
        $maxPage = ceil($books->getTotalItemCount() / $limit);
        //dd($books->count());

        return $this->render('admin/book/list.html.twig', [
            'books' => $books,
            'maxPage' => $maxPage,
            'page' => $page,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_book_delete', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function delete(?Book $book, Request $request, EntityManagerInterface $manager): Response
    {

        if ($book) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN');
        }

        $book ??= new Book();

        $manager->remove($book);
        $manager->flush();

        return $this->redirectToRoute('app_admin_book_list');

    }

    #[IsGranted('ROLE_AJOUT_DE_LIVRE')]
    #[Route('/new', name: 'app_admin_book_new', methods: ['GET', 'POST'])]
    #[Route('/{id}/edit', name: 'app_admin_book_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function new(?Book $book, Request $request, EntityManagerInterface $manager): Response
    {

        if ($book) {
            $this->denyAccessUnlessGranted('ROLE_EDITION_DE_LIVRE');
        }

        $book ??= new Book();
        $form = $this->createForm(BookType::class, $book);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($book);
            $manager->flush();

            return $this->redirectToRoute('app_admin_book_index');
        }

        return $this->render('admin/book/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_book_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(?Book $book): Response
    {
        return $this->render('admin/book/show.html.twig', [
            'book' => $book,
        ]);
    }
}