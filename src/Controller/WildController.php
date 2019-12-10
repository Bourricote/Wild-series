<?php
// src/Controller/WildController.php
namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Form\CommentType;
use App\Form\ProgramSearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/wild", name="wild_")
 */
class WildController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();

        if (!$programs) {
            throw $this->createNotFoundException(
                'No program found in program\'s table.'
            );
        }

        $form = $this->createForm(ProgramSearchType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $form->getData();
            $searchedProgram = $this->getDoctrine()
                ->getRepository(Program::class)
                ->findOneByTitle($data);
            return $this->render('wild/index.html.twig', [
                'programs' => $programs,
                'program' => $searchedProgram,
                'form' => $form->createView(),
            ]);
        }

        return $this->render('wild/index.html.twig', [
            'programs' => $programs,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Getting a program with a formatted slug for title
     *
     * @param string $slug The slugger
     * @Route("/show/{slug<^[a-z0-9-]+$>}", defaults={"slug" = null}, name="show")
     * @return Response
     */
    public function show(?string $slug):Response
    {
        if (!$slug) {
            throw $this
                ->createNotFoundException('No slug has been sent to find a program in program\'s table.');
        }
        $slug = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($slug)), "-")
        );
        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(['title' => mb_strtolower($slug)]);
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with '.$slug.' title, found in program\'s table.'
            );
        }

        return $this->render('wild/show.html.twig', [
            'program' => $program,
            'slug'  => $slug,
        ]);
    }

    /**
     * @param int|null $id
     * @Route("/category/{id}", defaults={"id" = null}, name="show_category")
     * @return Response
     */
    public function showByCategory(?int $id)
    {
        if (!$id) {
            throw $this->createNotFoundException('No id has been sent to find programs');
        }

        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findOneById($id);

        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findBy(['category' => $category]);

        if (!$programs) {
            throw  $this->createNotFoundException(
                'No programs in this category'
            );
        }

        return $this->render('wild/category.html.twig', [
            'programs' => $programs,
            'category' => $category,
        ]);
    }

    /**
     * @param int|null $id
     * @Route("/program/{id}", defaults={"id" = null}, name="show_program")
     * @return Response
     */
    public function showByProgram(?int $id):Response
    {
        if (!$id) {
            throw $this
                ->createNotFoundException('No id has been sent to find a program in program\'s table.');
        }

        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(['id' => $id]);
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with found in program\'s table.'
            );
        }

        $seasons = $program->getSeasons();
        $actors = $program->getActors();

        return $this->render('wild/showbyprogram.html.twig', [
            'program' => $program,
            'seasons' => $seasons,
            'actors' => $actors,
        ]);
    }

    /**
     * @param string|null $slug
     * @Route("/season/{id}", defaults={"id" = null}, name="show_season")
     * @return Response
     */
    public function showBySeason(int $id)
    {
        $season = $this->getDoctrine()->getRepository(Season::class)
            ->findOneBy(['id' => $id]);
        $program = $season->getProgram();
        $episodes = $season->getEpisodes();

        return $this->render('wild/showbyseason.html.twig', [
            'season' => $season,
            'program' => $program,
            'episodes'  => $episodes,
        ]);
    }

    /**
     * @param Episode $episode
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     * @Route("/episode/{id}", defaults={"id" = null}, name="show_episode")
     */
    public function showEpisode(Episode $episode, Request $request, EntityManagerInterface $em)
    {
        $season = $episode->getSeason();
        $program = $season->getProgram();
        $comments = $episode->getComments();

        $comment = new Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $author = $this->getUser();
            $comment->setEpisode($episode);
            $comment->setAuthor($author);
            $em->persist($comment);
            $em->flush();
            return $this->redirectToRoute('wild_show_episode',  ['id' => $episode->getId()]);
        }

        return $this->render('wild/showepisode.html.twig', [
            'episode' => $episode,
            'season' => $season,
            'program' => $program,
            'comments' => $comments,
            'form' => $form->createView(),
            ]);
    }

    /**
     * @Route("/comment/{id}/edit", name="comment_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Comment $comment
     * @return Response
     */
    public function editComment(Request $request, Comment $comment): Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('wild_show_episode',  ['id' => $comment->getEpisode()->getId()]);
        }

        return $this->render('wild/comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/comment/{id}", name="comment_delete", methods={"DELETE"})
     * @param Request $request
     * @param Comment $comment
     * @return Response
     */
    public function deleteComment(Request $request, Comment $comment): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('wild_show_episode',  ['id' => $comment->getEpisode()->getId()]);
    }
}
