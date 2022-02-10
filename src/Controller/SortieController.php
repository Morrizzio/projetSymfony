<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Route("/sorties", name="sortie_")
 */
class SortieController extends AbstractController
{

    /**
     * @Route("/create", name="create")
     */
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        EtatRepository $etatRepository,
        VilleRepository $villeRepository
    ): Response {
        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            $sortie->setEtat($etatRepository->find(1));
            $entityManager->persist($sortie);
            $entityManager->flush();
        }
        return $this->render('sortie/create.html.twig',['sortieForm' => $sortieForm->createView(), 'villes'=>$villeRepository->findAll()]);
    }

    /**
     * @Route("/details/{id}", name="details")
     */
    public function details(
        int $id,
        SortieRepository $sortieRepository
    ): Response{
        $sortie = $sortieRepository->find($id);
        if(!$sortie)
            throw $this->createNotFoundException('Sortie inexistante');

        return $this->render('sortie/detail.html.twig',['sortie'=>$sortie]);
    }

    /**
     * @Route("/cancel/{id}", name="cancel")
     */
    public function cancel(
        int $id,
        SortieRepository $sortieRepository
    ): Response{
        $sortie = $sortieRepository->find($id);
        /*
         * todo:Rajouter un form pour rajouter un motif d'annulation de la sortie
         */
        if(!$sortie)
            throw $this->createNotFoundException('Sortie inexistante');

        return $this->render('sortie/cancel.html.twig',['sortie'=>$sortie]);
    }

    /**
     * @Route("/modify/{id}", name="modify")
     */

    /**
     * @Route("/ajax", name="recherche_ajax")
     */
    public function ajaxAction(string $id,LieuRepository $lieuRepository,VilleRepository $villeRepository): Response
    {

            //$ville=$villeRepository->find((int)$id);
         dump('$id');
            $lieu = $lieuRepository->findBy(array('id'=>(int)$id));
            //dump($lieu);
            return $this->render('sortie/create.html.twig', ["lieu"=>$lieu]);

    }
}