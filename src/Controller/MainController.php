<?php

namespace App\Controller;

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
    public function home(){
        if($this->getUser() != null)
            return $this->render('main/home.html.twig');
        else
            return $this->redirectToRoute('app_login');
    }

}