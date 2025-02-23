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
public function modifierRendezVous(Request $request, RendezVous $rendezVous, EntityManagerInterface $entityManager): Response
{
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
    ]);
}


#[Route('medash/{id}', name: 'medecin_dashboard')]
    public function dashboard(Medecin $medecin): Response
    {
        return $this->render('consultation/meddash.html.twig', [
            'medecin' => $medecin,
        ]);
    }

    #[Route('/infomed/{id}', name: 'infomed')]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        $medecin = $entityManager->getRepository(Medecin::class)->find($id);

        if (!$medecin) {
            throw $this->createNotFoundException('Médecin non trouvé.');
        }

        return $this->render('consultation/infomed.html.twig', [
            'medecin' => $medecin,
        ]);
    }


}