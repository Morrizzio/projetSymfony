<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Repository\LieuRepository;
use App\Repository\SortieRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use JsonSerializable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;


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
        VilleRepository $villeRepository,
        LieuRepository $lieuRepository
    ): Response {
        $sortie = new Sortie();
        $sortieForm = $this->createForm(SortieType::class, $sortie);
        $sortieForm->handleRequest($request);

        if($sortieForm->isSubmitted() && $sortieForm->isValid()) {
            dump($_POST);

            $sortie->setLieu($lieuRepository->find($_POST['lieux']));
            $sortie->setEtat($etatRepository->find(1));
            dump($sortie);
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
    public function ajaxAction(LieuRepository $lieuRepository,VilleRepository $villeRepository): Response
    {
        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);
        $id = $_POST['id'];

         $lieux = $lieuRepository->findBy(array('ville'=>(int)$id));
         $jsonContent = $serializer->serialize($lieux, 'json');
         dump($lieux);
        return new Response(json_encode($jsonContent));
    }

    /**
     * @Route("/inscription/{id}", name="inscription")
     */
    public function addParticipant(EntityManagerInterface $entityManager, SortieRepository $sortieRepository, int $id){
        $participant = $this->getUser();
        $sortie = $sortieRepository->find($id);

        $sortie->addParticipant($participant);
        $entityManager->persist($sortie);
        $entityManager->flush();
        $this->addFlash('success', 'Inscription enregistée');

        return $this->render('sortie/detail.html.twig',[
            'sortie' => $sortie
        ]);
    }

}