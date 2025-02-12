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


final class ReclamationController extends AbstractController
{
    #[Route('/liste', name: 'liste_reclamation')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $reclamations = $entityManager->getRepository(Reclamation::class)->findAll();

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
            // Récupérer un patient existant (modifie l'ID en fonction de ton besoin)
            $patient = $entityManager->getRepository(Patient::class)->find(1);
    
            if (!$patient) {
                throw $this->createNotFoundException("Le patient n'existe pas !");
            }
    
            $reclamation->setPatient($patient); // Associe le patient
    
            $entityManager->persist($reclamation);
            $entityManager->flush();
    
            $this->addFlash('success', 'Réclamation ajoutée avec succès !');
            return $this->redirectToRoute('liste_reclamation');
        }
    
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
            return $this->redirectToRoute('liste_reclamation');
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

        return $this->redirectToRoute('liste_reclamation');
    }
}
