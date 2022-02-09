<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", name="main_")
 */
class MainController extends AbstractController
{

    /**
     * @Route("", name="home")
     */
    public function home(SortieRepository $sortieRepository){
        if($this->getUser() != null){
            $sorties = $sortieRepository->findAll();
            return $this->render('main/home.html.twig', ["sorties"=>$sorties]);
        }
        else{
            return $this->redirectToRoute('app_login');
        }
    }
    /**
     * @Route("/creersortie", name="main_creersortie")
     */
    public function creerSortie(
        Request $request,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository
    ): Response{
        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);
        if($sortieForm->isSubmitted()){
            //$sortie->setParticipant(null);

            $sortie->setEtat($etatRepository->find(1));
            $entityManager->persist($sortie);
            $entityManager->flush();
        }
        return $this->render('sortie/create.html.twig', ['sortieForm' => $sortieForm->createView()]);
    }
}