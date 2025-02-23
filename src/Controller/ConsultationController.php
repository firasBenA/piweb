<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Entity\Patient;
use App\Entity\Medecin;
use App\Entity\User;
use App\Form\RendezVousType;
use App\Form\ModifConType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ConsultationController extends AbstractController
{
    #[Route('/consultation', name: 'app_consultation')]
    public function index(): Response
    {
        return $this->render('consultation/index.html.twig', [
            'controller_name' => 'ConsultationController',
        ]);
    }

    #[Route('/listcon', name: 'consultation_medecin_list')]
    public function listForMedecin(Security $security, EntityManagerInterface $entityManager): Response
    {
        // Get the logged-in medecin (assumed that user is a Medecin)
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You are not logged in or not a medecin.');
        }

        // Fetch the rendezvous for the logged-in medecin
        $rendezVous = $entityManager->getRepository(RendezVous::class)->findBy(['medecin' => $user]);

        return $this->render('consultation/listcon.html.twig', [
            'user' => $user,
            'rendezVous' => $rendezVous,
        ]);
    }



    #[Route('/approuver/{id}', name: 'rendezvous_approuver')]
    public function approuverRendezVous(RendezVous $rendezVous, EntityManagerInterface $entityManager): Response
    {

        $rendezVous->setStatut('Approuvé');

        $entityManager->flush();


        $this->addFlash('success', 'Le rendez-vous a été approuvé.');


        return $this->redirectToRoute('consultation_medecin_list', ['id' => $rendezVous->getMedecin()->getId()]);
    }


    #[Route('/refuser/{id}', name: 'rendezvous_refuser')]
    public function refuserRendezVous(RendezVous $rendezVous, EntityManagerInterface $entityManager): Response
    {

        $rendezVous->setStatut('Refusé');
        $entityManager->flush();

        $this->addFlash('danger', 'Le rendez-vous a été refusé.');

        return $this->redirectToRoute('consultation_medecin_list', ['id' => $rendezVous->getMedecin()->getId()]);
    }

    #[Route('/modifier/{id}', name: 'rendezvous_modifier')]
    public function modifierRendezVous(Request $request, RendezVous $rendezVous, EntityManagerInterface $entityManager, Security $security): Response
    {
        // Get the logged-in user
        $user = $security->getUser();

        // Check if the logged-in user is either a doctor or a patient associated with the rendez-vous
        if (!$user || (!in_array('ROLE_MEDECIN', $user->getRoles()) && $user !== $rendezVous->getPatient()->getUser())) {
            // If the user is not authorized to modify the rendez-vous, deny access
            throw $this->createAccessDeniedException("Vous n'avez pas l'autorisation de modifier ce rendez-vous.");
        }

        // Créer le formulaire avec l'entité RendezVous
        $form = $this->createForm(ModifConType::class, $rendezVous);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Vérifier si la date est vide après soumission
            if ($rendezVous->getDate() === null) {
                // Ajouter un message d'erreur si la date est vide
                $this->addFlash('error', 'Veuillez remplir la date.');
                // Optionnel : retourner à la vue pour afficher le formulaire à nouveau
                return $this->render('consultation/modifier.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Si le formulaire est valide, mettre à jour le statut
            if ($form->isValid()) {
                $rendezVous->setStatut('Approuvé');
                $entityManager->flush();

                // Message de succès
                $this->addFlash('success', 'Le rendez-vous a été modifié et approuvé.');

                // Rediriger vers la liste des consultations
                return $this->redirectToRoute('consultation_medecin_list', ['id' => $rendezVous->getMedecin()->getId()]);
            } else {
                // En cas d'erreur de validation, vous pouvez gérer cela ici si nécessaire
                $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
            }
        }

        return $this->render('consultation/modifier.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }



    #[Route('medash', name: 'medecinDashboard')]
    public function dashboard(Security $security): Response
    {
        // Get the logged-in user
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You are not logged in.');
        }

        // Render the dashboard page with the logged-in user
        return $this->render('consultation/meddash.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/infomed', name: 'infomed')]
    public function show(Security $security, EntityManagerInterface $entityManager): Response
    {
        // Get the logged-in medecin (assumed that user is a Medecin)
        $medecin = $security->getUser();

        if (!$medecin) {
            throw $this->createAccessDeniedException('You are not logged in or not a medecin.');
        }

        // Render the template with the logged-in medecin
        return $this->render('consultation/infomed.html.twig', [
            'medecin' => $medecin,
        ]);
    }
}
