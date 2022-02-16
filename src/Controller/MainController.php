<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SortieFiltreType;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
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
    public function home(SortieRepository $sortieRepository):Response{

        if($this->getUser() != null){
            $idUser=$this->getUser();

            $sorties = $sortieRepository->findAll();
            $sortieFiltreForm = $this->createForm(SortieFiltreType::class);
            return $this->render('main/home.html.twig', ["sorties"=>$sorties,
                'sortieFiltreForm' => $sortieFiltreForm->createView(),
                "utilisateur"=>$idUser,
                ]);
        }
        else{
            return $this->redirectToRoute('app_login');
        }
    }
}