<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Participant;
use App\Form\CsvImportFormType;
use App\Form\ParticipantType;
use App\Form\RegistrationFormType;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

/**
 * @Route("/participant", name="participant_")
 */
class ParticipantController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(ParticipantRepository $participantRepository, Request $request): Response
    {
        $limit = 15;
        $noPage = $request->query->get("page",1);
        $participants = $participantRepository->paginator($limit, $noPage);
        $total = $participantRepository->getTotalParticipants();
        return $this->render('participant/index.html.twig', [
            'participants' => $participants,
            'total' => $total,
            'limit' => $limit,
            'page' => $noPage
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function new(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, AppAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $participant = new Participant();
        $participant->setRoles(['ROLE_USER']);
        $participant->setActif(true);

        if($participant->getRoles() == ['ROLE_USER'])
            $participant->setAdministrateur(true);
        else
            $participant->setAdministrateur(false);

        $participant->setAdministrateur(true);
        $form = $this->createForm(RegistrationFormType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $participant->setPassword(
                $userPasswordHasher->hashPassword(
                    $participant,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($participant);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $userAuthenticator->authenticateUser(
                $participant,
                $authenticator,
                $request
            );

        }

        return $this->render('participant/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/details/{id}", name="details")
     */
    public function details(
        int $id,
        ParticipantRepository $participantRepository
    ): Response{

        $participant = $participantRepository->find($id);
        if(!$participant)
            throw $this->createNotFoundException('Utilisateur inconnue');

        return $this->render('participant/detail.html.twig', ['participant' => $participant]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager,
        ParticipantRepository $participantRepository
    ):Response{
        $participant = $participantRepository->find($id);
        if(!$participant)
            throw $this->createNotFoundException('Utilisateur inconnue');

        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'modification(s) sur le profil enregistée(s)');
            return $this->redirectToRoute('participant_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('participant/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/self", name="self")
     */
    public function self(
        Request $request,
        EntityManagerInterface $entityManager): Response
    {
        $participant = $this->getUser();
        $form = $this->createForm(ParticipantType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupèrer les informations du formaulire dans un objet $file
            $file = $form->get('champ')->getData();
            if ($file)
            {
                // On renomme le fichier, selon une convention propre au projet
                // Par exemple nom de l'entité + son id + extension soit 'entite-1.jpg'

                $newFilename = $participant->getPseudo()."-".$participant->getId().".".$file->guessExtension();
                $file->move($this->getParameter('upload_champ_entite_dir'), $newFilename);
                $participant->setChamp($newFilename);
            }
            $entityManager->flush();
            $this->addFlash('success', 'modification(s) sur le profil enregistée(s)');
            return $this->redirectToRoute('participant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('participant/edit.html.twig', [
            'participant' => $participant,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function delete(Request $request, int $id ,EntityManagerInterface $entityManager,ParticipantRepository $participantRepository): Response
    {
        $participant=$participantRepository->find($id);
        if(!$participant)
            throw $this->createNotFoundException('Utilisateur inconnue');

        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {

            $entityManager->remove($participant);
            $entityManager->flush();
        }
        return $this->redirectToRoute('participant_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/deleter", name="deleter")
     */
    public function deleter(Request $request, EntityManagerInterface $entityManager, ParticipantRepository $participantRepository): Response
    {
        $deleteP = $request->get('cb');
        if($this->isCsrfTokenValid('delete-items', $request->request->get('_token'))) {
            if($deleteP!=null) {
                foreach ($deleteP as $key => $item) {
                    $participant = $participantRepository->find($key);
                    $entityManager->remove($participant);
                }
            }
            $entityManager->flush();
            return $this->redirectToRoute('participant_index', [], Response::HTTP_SEE_OTHER);
        }else{
            throw $this->createAccessDeniedException();
        }
    }

    /**
     * @Route("/addWithFile",name="addWithFile")
     */
    public function addWithFile(Request $request, CampusRepository $campusRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
    {
        $form = $this->createForm(CsvImportFormType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $file = $form->get('champ')->getData();
            $newFileName = "datas.csv";
            if($file){
                $file->move($this->getParameter('upload_csv_dir'), $newFileName);
                $handle = fopen($this->getParameter('upload_csv_dir')."/datas.csv",'r');

                $mail = null;
                $pseudo = null;
                $nom = null;
                $prenom = null;
                $tel = null;
                $campus = null;

                $tour = 0;
                while(($data = fgetcsv($handle)) !== false){
                    if($tour == 0){
                        foreach($data as $key => $value){
                            if($value == "email")
                                $mail = $key;
                            if($value == "pseudo")
                                $pseudo = $key;
                            if($value == "nom")
                                $nom = $key;
                            if($value == "prenom")
                                $prenom = $key;
                            if($value == "telephone")
                                $tel = $key;
                            if($value == "campus")
                                $campus = $key;
                        }
                    }else{
                        $participant = new Participant();
                        $participant
                            ->setPseudo($data[$pseudo])
                            ->setNom($data[$nom])
                            ->setPrenom($data[$prenom])
                            ->setEmail($data[$mail])
                            ->setTelephone($data[$tel])

                            ->setRoles(["ROLE_USER"])
                            ->setPassword($userPasswordHasher->hashPassword($participant,'Az123@'))
                            ->setAdministrateur(0)
                            ->setActif(1);
                        if($campus != null){
                            $oCampus = $campusRepository->findOneBy([
                                'nom' => $data[$campus]
                            ]);
                            if($oCampus === null){
                                $campus = (new Campus())
                                    ->setNom($data[$campus]);
                                $entityManager->persist($oCampus);
                                $entityManager->flush();
                            }
                            $participant->setCampus($oCampus);
                        }
                        $entityManager->persist($participant);
                    }
                    $tour++;
                }
                $entityManager->flush();
            }
            $this->addFlash('success', 'Tous les participants ont été ajouter a la base de donnée');
            return $this->redirectToRoute('participant_index');
        }
        return $this->render('participant/addfile.html.twig',[
            'form' => $form->createView()
        ]);
    }
}
