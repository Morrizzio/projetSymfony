<?php

namespace App\Controller;

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
            return $this->render('main/home.html.twig', ["sorties"=>$sorties]);
        }
        else{
            return $this->redirectToRoute('app_login');
        }
    }
}