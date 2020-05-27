<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Security\Voter\UserVoter;
use App\Service\SlugGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PostsController.
 */
class PostsController extends PagesController
{
    /* ************************************************ *
     * actualites
     * ************************************************ */

    /**
     * @Route("/actualites/{page<\d+>}", name="actualites")
     */
    public function actualitesIndex(
        Request $request,
        int $page = 1
    ) {
        $limit = 10;
        $articleRepository = $this->getDoctrine()->getRepository(Post::class)->setPosts('article');
        $articles = $articleRepository->findAllPaginatedPosts($page, $limit);
        $lastPage = ceil(count($articleRepository->findAll()) / $limit);

        return $this->render('pages/actualites.html.twig', [
            'breadcrumbs' => $this->breadcrumbsGenerator->getBreadcrumbs(str_replace('/'.$page, '', $request->getPathInfo())),
            'articles' => $articles,
            'pagination' => [
                'currentPage' => $page,
                'lastPage' => $lastPage,
            ],
        ]);
    }

    /* ************************************************ *
     * article
     * ************************************************ */

    /**
     * @Route("/actualites/{slug<\d*\/\d*\/.+>}", name="actualites_show")
     */
    public function articlePage(
        Request $request,
        string $slug
    ) {
        $articleRepository = $this->getDoctrine()->getRepository(Post::class)->setPosts('article');
        $article = $articleRepository->findBySlug($slug);
        if (null === $article) {
            throw new NotFoundHttpException('La page demandée n’existe pas');
        }

        $nextPreviousArticles = $articleRepository->findNextPrevious($article->getId());

        $activePageBreadCrumb = [
            'slug' => $slug,
            'title' => $article->getTitle(),
        ];

        return $this->render('pages/article.html.twig', [
            'breadcrumbs' => $this->breadcrumbsGenerator->getBreadcrumbs($request->getPathInfo(), $activePageBreadCrumb),
            'article' => $article,
            'nextPreviousArticles' => $nextPreviousArticles,
        ]);
    }

    /**
     * @Route("/actualites/new", name="post_create")
     */
    public function postNew(
        Request $request,
        EntityManagerInterface $manager,
        SlugGenerator $slugGenerator
    ) {
        $this->denyAccessUnlessGranted(UserVoter::LOGGED);

        $post = new Post();

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($this->getUser());
            $post->setCreatedAt(new \DateTime());
            $post->setSlug($slugGenerator->generateSlug($post->getTitle(), $post->getCreatedAt()));

            $manager->persist($post);
            $manager->flush();

            $this->addFlash('notice', 'L’article a été créé');

            return $this->redirectToRoute('actualites');
        }

        return $this->render('pages/post/post-create.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/actualites/{postId}/edit", name="post_edit")
     */
    public function postEdit(
        $postId,
        Request $request,
        EntityManagerInterface $manager
    ) {
        $this->denyAccessUnlessGranted(UserVoter::LOGGED);

        $post = $manager->getRepository(Post::class)
            ->findById($postId);

        if (!$post) {
            throw $this->createNotFoundException('L’article n’existe pas');
        }

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setEditedAt(new \DateTime());

            $manager->flush();

            $this->addFlash('notice', 'L’article a été modifié');

            return $this->redirectToRoute('actualites');
        }

        return $this->render();
    }

    /**
     * @Route("/actualites/{postId}/delete", name="post_delete")
     */
    public function postDelete(
        $postId,
        Request $request,
        EntityManagerInterface $manager
    ) {
        $post = $manager->getRepository(Post::class)
            ->findById($postId);

        if (!$post) {
            throw $this->createNotFoundException('L’article n’existe pas');
        }

        $form = $this->createFormBuilder()
            ->add('submit', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->remove($post);
            $manager->flush();

            $this->addFlash('notice', 'L’article a été supprimé');

            return $this->redirectToRoute('actualites');
        }

        return $this->render('pages/confirm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /* ************************************************ *
     * evenements
     * ************************************************ */

    /**
     * @Route("/evenements/{page<\d+>}", name="evenements")
     */
    public function evenementsIndex(
        Request $request,
        int $page = 1
    ) {
        $limit = 10;
        $eventRepository = $this->getDoctrine()->getRepository(Post::class)->setPosts('event');
        $events = $eventRepository->findAllPaginatedPosts($page, $limit);
        $lastPage = ceil(count($eventRepository->findAll()) / $limit);

        return $this->render('pages/evenements.html.twig', [
            'breadcrumbs' => $this->breadcrumbsGenerator->getBreadcrumbs(str_replace('/'.$page, '', $request->getPathInfo())),
            'events' => $events,
            'pagination' => [
                'currentPage' => $page,
                'lastPage' => $lastPage,
            ],
        ]);
    }

    /* ************************************************ *
     * event
     * ************************************************ */

    /**
     * @Route("/evenements/{slug<\d*\/?\d*\/?.+>}", name="evenements_show")
     */
    public function eventPage(
        Request $request,
        string $slug
    ) {
        $eventRepository = $this->getDoctrine()->getRepository(Post::class)->setPosts('event');
        $event = $eventRepository->findBySlug($slug);
        if (null === $event) {
            throw new NotFoundHttpException('La page demandée n’existe pas');
        }

        $nextPreviousEvents = $eventRepository->findNextPrevious($event->getId());

        $activePageBreadCrumb = [
            'slug' => $slug,
            'title' => $event->getTitle(),
        ];

        return $this->render('pages/event.html.twig', [
            'breadcrumbs' => $this->breadcrumbsGenerator->getBreadcrumbs($request->getPathInfo(), $activePageBreadCrumb),
            'event' => $event,
            'nextPreviousEvents' => $nextPreviousEvents,
        ]);
    }
}
