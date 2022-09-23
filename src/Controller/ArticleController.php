<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use App\Repository\CommentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

class ArticleController extends AbstractController
{
    #[Route('/', name: 'app_article_index', methods: ['GET'])]
    public function index(ArticleRepository $articleRepository, PaginatorInterface $paginator,Request $request): Response
    {
        $data = $articleRepository->findAll();
        $articles = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1), /*page number*/
            4 /*limit per page*/
        );
        $articles->setTemplate('article/bootstrap_v5_pagination.html.twig');
        $articles->setSortableTemplate('article/bootstrap_v5_fa_sortable_link.html.twig');

        // parameters to template
        return $this->render('article/index.html.twig', ['articles' => $articles]);
    }

    #[Route('/article/new', name: 'app_article_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ArticleRepository $articleRepository): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);
        $article->setCreatedAt(new \DateTime());
        $article->setUser($this->getUser());
        if($this->isGranted('ROLE_ADMIN')) {
            $form->add('user');
        }
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $articleRepository->add($article, true);

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('article/new.html.twig', [
            'article' => $article,
            'form' => $form,
        ]);
    }

    #[Route('/article/{id}', name: 'app_article_show')]
    public function show(ArticleRepository $articleRepo, CommentRepository $commentRepo, $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comments =$commentRepo->findBy(['article' => $id]);
        $article =$articleRepo->findOneBy(['id' => $id]);

        $commentForm = $this->createForm(CommentType::class, new Comment());
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {

            $comment = $commentForm->getData();
            $comment->setArticle($article);
            $comment->setUser($this->getUser());
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('app_article_show', ['id' => $id]);
        }

        return $this->render('article/show.html.twig', [
            'article' => $article,
            'comments' => $article->getComments(),
            'form' => $commentForm->createView(),
        ]);
    }

    #[Route('/article/{id}/edit', name: 'app_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Article $article, ArticleRepository $articleRepository): Response
    {
        if($article->getUser()->getId() == $this->getUser()->getId() or $this->isGranted('ROLE_ADMIN')) {
            $form = $this->createForm(ArticleType::class, $article);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $articleRepository->add($article, true);

                return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->renderForm('article/edit.html.twig', [
                'article' => $article,
                'form' => $form,
            ]);
        } else {
            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }
    }

    #[Route('/article/{id}', name: 'app_article_delete', methods: ['POST'])]
    public function delete(Request $request, Article $article, ArticleRepository $articleRepository): Response
    {
        if($article->getUser()->getId() == $this->getUser()->getId() or $this->isGranted('ROLE_ADMIN')) {
            if ($this->isCsrfTokenValid('delete'.$article->getId(), $request->request->get('_token'))) {
                $articleRepository->remove($article, true);
            }
        } else {
            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/article/comment/delete/{id}', name: 'app_comment_delete', methods: ['POST'])]
    public function commentdelete(Request $request, Comment $comment, CommentRepository $commentRepository): Response
    {
        if($comment->getUser()->getId() == $this->getUser()->getId() or $this->isGranted('ROLE_ADMIN')) {
            $commentRepository->remove($comment, true);
        } else {
            return $this->redirectToRoute('/article/{id}', [], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }
}
