<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Form\RegistrationFormType;
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
    public function index(ParticipantRepository $participantRepository): Response
    {
        return $this->render('participant/index.html.twig', [
            'participants' => $participantRepository->findAll(),
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

}
