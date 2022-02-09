<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
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
    public function home(){
        if($this->getUser() != null)
            return $this->render('main/home.html.twig');
        else
            return $this->redirectToRoute('app_login');
    }
    /**
     * @Route("/creersortie", name="main_creersortie")
     */
    public function creerSortie(Request $request): Response{
        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);
        return $this->render('sortie/create.html.twig', ['sortieForm' => $sortieForm->createView()]);
    }
}