<?php

namespace App\Controller;

use App\Form\SortieFiltreType;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            $sortieFiltreForm = $this->createForm(SortieFiltreType::class);
            return $this->render('main/home.html.twig', ["sorties"=>$sorties, 'sortieFiltreForm' => $sortieFiltreForm->createView()]);
        }
        else{
            return $this->redirectToRoute('app_login');
        }
    }
}