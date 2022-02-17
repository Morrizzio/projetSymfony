<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\LieuType;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/lieu", name="lieu_")
 */
class LieuController extends AbstractController
{
    /**
     * @Route("/", name="detail")
     */
    public function index(): Response
    {
        return $this->render('lieu/index.html.twig', [
            'controller_name' => 'LieuController',
        ]);
    }
    /**
     * @Route("/create", name="create")
     */
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        VilleRepository $villeRepository
    ): Response
    {
        $lieu = new Lieu();
        $lieuForm = $this->createForm(LieuType::class, $lieu);
        $lieuForm->handleRequest($request);

        if($lieuForm->isSubmitted() && $lieuForm->isValid()) {
            dump($lieu);
            $entityManager->persist($lieu);
            $entityManager->flush();
        }
        return $this->render('lieu/create.html.twig',
            ['lieuForm' => $lieuForm->createView(),
                'villes'=>$villeRepository->findAll()]);
    }
}
