<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Entity\Patient;
use App\Entity\Medecin;
use App\Form\RendezVousType;
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
    // Crée le formulaire lié à l'entité RendezVous
    $form = $this->createForm(RendezVousType::class, $rendezVous);
    $form->handleRequest($request);

    // Si le formulaire est soumis et valide
    if ($form->isSubmitted() && $form->isValid()) {
        // Récupère la nouvelle date du formulaire
        $newDate = $rendezVous->getDate();

        // Vérifie si la date est dans le passé
        if ($newDate < new \DateTime('today')) {
            // Message flash pour indiquer que la date choisie est invalide
            $this->addFlash('error', 'La date choisie ne peut pas être antérieure à aujourd\'hui.');

            // Redirige vers la même page pour afficher le formulaire avec l'erreur
            return $this->redirectToRoute('rendezvous_modifier', ['id' => $rendezVous->getId()]);
        }

        // Met à jour la date du rendez-vous et change son statut en "Approuvé"
        $rendezVous->setDate($newDate);
        $rendezVous->setStatut('Approuvé'); // Changer le statut automatiquement à "Approuvé"

        // Sauvegarde les changements dans la base de données
        $entityManager->flush();

        // Message flash pour indiquer que le rendez-vous a été modifié et approuvé
        $this->addFlash('success', 'Le rendez-vous a été modifié et approuvé.');

        // Redirection vers la liste des rendez-vous du médecin
        return $this->redirectToRoute('consultation_medecin_list', ['id' => $rendezVous->getMedecin()->getId()]);
    }

    // Rendu du formulaire dans la vue Twig
    return $this->render('consultation/modifier.html.twig', [
        'form' => $form->createView(),
    ]);
}

}
