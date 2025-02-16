<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Entity\Patient; // Ajout de l'entité Patient si elle est liée
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ReclamationController extends AbstractController
{
    #[Route('/liste', name: 'reclamation_page')]
    public function index(EntityManagerInterface $entityManager, Request $request): Response
    {
        // Get the 'etat' parameter from the request (query string)
        $etat = $request->query->get('etat');
    
        // Check if the 'etat' is provided and apply the filter accordingly
        if ($etat) {
            // Filter by the selected state
            $reclamations = $entityManager->getRepository(Reclamation::class)
                ->findBy(['etat' => $etat], ['date_debut' => 'DESC']);
        } else {
            // If no filter, fetch all reclamations
            $reclamations = $entityManager->getRepository(Reclamation::class)
                ->findBy([], ['date_debut' => 'DESC']);
        }
    
        return $this->render('reclamation/liste.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }
    

    #[Route('/ajouter', name: 'ajouter_reclamation')]
    public function ajouter(Request $request, EntityManagerInterface $entityManager): Response
    {
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer un patient existant (remplace l'ID par un ID valide)
            $patient = $entityManager->getRepository(Patient::class)->find(1);
    
            if (!$patient) {
                throw $this->createNotFoundException("Le patient avec l'ID 1 n'existe pas !");
            }
    
            // Associer la réclamation au patient
            $reclamation->setPatient($patient);
            $reclamation->setEtat('en_attente');
            $reclamation->setDateDebut(new \DateTime());
    
            // Persister la réclamation dans la base de données
            $entityManager->persist($reclamation);
            $entityManager->flush();
    
            // Ajouter un message flash de succès
            $this->addFlash('success', 'Réclamation ajoutée avec succès !');
    
            // Rediriger vers la liste des réclamations
            return $this->redirectToRoute('reclamation_page');
        }
    
        // Rendre le formulaire d'ajout de réclamation
        return $this->render('reclamation/ajouterrec.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/modifier/{id}', name: 'modifier_reclamation')]
    public function modifier(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Flash message
            $this->addFlash('success', 'Réclamation mise à jour avec succès !');

            return $this->redirectToRoute('reclamation_page');
        }

        return $this->render('reclamation/modifier.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/supprimer/{id}', name: 'supprimer_reclamation')]
    public function supprimer(Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($reclamation);
        $entityManager->flush();

        return $this->redirectToRoute('reclamation_page');
    }
}
