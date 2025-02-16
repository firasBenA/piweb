<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Entity\Patient;
use App\Entity\Medecin;
use App\Form\RendezVousType;
use App\Form\ModifConType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route('/listcon/{id}/', name: 'consultation_medecin_list')]
public function listForMedecin(Medecin $medecin, EntityManagerInterface $entityManager): Response
{
    $rendezVous = $entityManager->getRepository(RendezVous::class)->findBy(['medecin' => $medecin]);

    return $this->render('consultation/listcon.html.twig', [
        'medecin' => $medecin,
        'rendezVous' => $rendezVous,
    ]);
}


// Route pour approuver un rendez-vous
#[Route('/approuver/{id}', name: 'rendezvous_approuver')]
public function approuverRendezVous(RendezVous $rendezVous, EntityManagerInterface $entityManager): Response
{
    // Change le statut du rendez-vous en "Approuvé"
    $rendezVous->setStatut('Approuvé');
    // Sauvegarde les changements en base de données
    $entityManager->flush();

    // Message flash pour indiquer que le rendez-vous a été approuvé
    $this->addFlash('success', 'Le rendez-vous a été approuvé.');

    // Redirection vers la liste des rendez-vous du médecin
    return $this->redirectToRoute('consultation_medecin_list', ['id' => $rendezVous->getMedecin()->getId()]);
}

// Route pour refuser un rendez-vous
#[Route('/refuser/{id}', name: 'rendezvous_refuser')]
public function refuserRendezVous(RendezVous $rendezVous, EntityManagerInterface $entityManager): Response
{
    // Change le statut du rendez-vous en "Refusé"
    $rendezVous->setStatut('Refusé');
    // Sauvegarde les changements en base de données
    $entityManager->flush();

    // Message flash pour indiquer que le rendez-vous a été refusé
    $this->addFlash('danger', 'Le rendez-vous a été refusé.');

    // Redirection vers la liste des rendez-vous du médecin
    return $this->redirectToRoute('consultation_medecin_list', ['id' => $rendezVous->getMedecin()->getId()]);
}

// Route pour modifier la date d'un rendez-vous
#[Route('/modifier/{id}', name: 'rendezvous_modifier')]
    public function modifierRendezVous(Request $request, RendezVous $rendezVous, EntityManagerInterface $entityManager): Response
    {
        // Crée un formulaire avec uniquement le champ date
        $form = $this->createForm(ModifConType::class, $rendezVous);
        $form->handleRequest($request);

        // Vérification et traitement du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            $newDate = $rendezVous->getDate();

            // Vérifie que la date n'est pas dans le passé
            if ($newDate < new \DateTime('today')) {
                $this->addFlash('error', 'La date choisie ne peut pas être antérieure à aujourd\'hui.');
                return $this->redirectToRoute('rendezvous_modifier', ['id' => $rendezVous->getId()]);
            }

            // Met à jour le rendez-vous et change son statut en "Approuvé"
            $rendezVous->setStatut('Approuvé');
            $entityManager->flush();

            $this->addFlash('success', 'Le rendez-vous a été modifié et approuvé.');

            // Redirection après modification
            return $this->redirectToRoute('consultation_medecin_list', ['id' => $rendezVous->getMedecin()->getId()]);
        }

        // Rendu du formulaire dans la vue
        return $this->render('consultation/modifier.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
