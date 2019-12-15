<?php

// src/Controller/WildController.php
namespace App\Controller;

use App\Entity\Category;
use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WildController extends AbstractController
{
    /**
     * @Route("wild/index", name="wild_index")
     * @return Response A response instance
     */
    public function index() :Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();

        if (!$programs) {
            throw $this->createNotFoundException(
                'No program found in program\'s table.'
            );
        }

        return $this->render(
            'wild/index.html.twig',
            [ 'programs' => $programs ]
        );
    }

    /**
     * @param string $slug The slugger
     * @Route(
     *     "wild/show/{slug}",
     *     defaults={"slug"=""},
     *     requirements={"slug"="[a-z0-9\-]+"},
     *     name="wild_show"
     * )
     * @return Response
     */
    public function show(?string $slug) : Response
    {
        if(!$slug) {
            throw $this->createNotFoundException(
                'No slug has been sent to find a program in program\'s table.'
            );
        }
        $slug = preg_replace('/-/', ' ', ucwords(trim(strip_tags($slug))));
        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(['title' => mb_strtolower($slug)]);
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with ' . $slug . ' title, found in program\'s table.'
            );
        }
        return $this->render('wild/show.html.twig', [
            'program' => $program,
            'slug' => $slug
        ]);
    }

    /**
     * @param string $categoryName
     * @Route(
     *     "/wild/category/{categoryName}",
     *     defaults={"categoryName"=""},
     *     name="show_category"
     * )
     * @return Response
     */
    public function showByCategory(string $categoryName) : Response
    {
        if (!$categoryName) {
            throw $this->createNotFoundException(
                'No category ' . $categoryName . ' found in category\'s table.'
            );
        }
        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findOneBy(['name' => $categoryName]);
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findBy(['category' => $category], ['id' => 'desc'], 3, 0);
        if (!$programs) {
            throw $this->createNotFoundException(
                'No programs found in program\'s table with category\'s ' . $categoryName . ' name.'
            );
        }
        return $this->render('wild/category.html.twig',
            [
                'categoryName' => $categoryName,
                'programs' => $programs
            ]);
    }

    /**
     * @param int|null $id
     * @Route(
     *     "/wild/program/{id}",
     *     defaults={"id"=null},
     *     name="show_program"
     * )
     * @return Response
     */
    public function showByProgram(?int $id): Response
    {
        if (!$id) {
            throw $this->createNotFoundException(
                'No program found for this category.'
            );
        }
        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(['id' => $id]);
        $seasons = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findBy(['program' => $program], ['id' => 'ASC']);
        if (!$seasons) {
            throw $this->createNotFoundException(
                'No seasons found in season\'s table.'
            );
        }
        return $this->render('wild/program.html.twig',
            [
                'program' => $program,
                'seasons' => $seasons
            ]);
    }

    /**
     * @param int|null $id
     * @Route(
     *     "/wild/season/{id}",
     *     defaults={"id"=null},
     *     name="show_season"
     * )
     * @return Response
     */
    public function showBySeason(?int $id): Response
    {
        if (!$id) {
            throw $this->createNotFoundException(
                'No season found for this program.'
            );
        }
        $seasons = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findOneBy(['id' => $id]);
        $program = $seasons->getProgram();
        $episodes = $seasons->getEpisodes();

        return $this->render('wild/season.html.twig',
            [
                'program' => $program,
                'seasons' => $seasons,
                'episodes' => $episodes
            ]);
    }
}
