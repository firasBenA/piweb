<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Entity\Patient;
use App\Form\RendezVousType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RendezVousController extends AbstractController
{
    #[Route('/addrendezvous/{id}', name: 'addrendezvous')]
    public function addRendezVous(ManagerRegistry $rm, Request $req, int $id): Response
    {
        $entityManager = $rm->getManager();
        $patient = $entityManager->getRepository(Patient::class)->find($id);

        if (!$patient) {
            throw $this->createNotFoundException("Le patient avec l'ID $id n'existe pas.");
        }

        $rdv = new RendezVous();
        $form = $this->createForm(RendezVousType::class, $rdv);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification de la date du rendez-vous
            if ($rdv->getDate() < new \DateTime('today')) {
                $this->addFlash('error', 'Vous ne pouvez pas choisir une date antérieure à aujourd\'hui.');
                return $this->redirectToRoute('addrendezvous', ['id' => $id]);
            }

            // Affecter le patient et le statut du rendez-vous
            $rdv->setPatient($patient);
            $rdv->setStatut('en attente');

            // Sauvegarder le rendez-vous dans la base de données
            $entityManager->persist($rdv);
            $entityManager->flush();

            $this->addFlash('success', 'Votre rendez-vous a été enregistré avec succès.');

            // Rediriger vers la liste des rendez-vous du patient
            return $this->redirectToRoute('listrdv', ['id' => $id]);
        }

        return $this->render('rendez_vous/addrdv.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/listrdv/{id}', name: 'listrdv')]
    public function listRendezVous(ManagerRegistry $rm, int $id): Response
    {
        $entityManager = $rm->getManager();
        $patient = $entityManager->getRepository(Patient::class)->find($id);

        if (!$patient) {
            throw $this->createNotFoundException("Le patient avec l'ID $id n'existe pas.");
        }

        // Récupérer les rendez-vous du patient
        $rendezVous = $entityManager->getRepository(RendezVous::class)->findBy(['patient' => $patient]);

        // Ajouter un message flash pour indiquer la possibilité de réserver un rendez-vous
        $this->addFlash('info', 'Vous pouvez réserver un nouveau rendez-vous.');

        return $this->render('rendez_vous/listrdv.html.twig', [
            'rendezVous' => $rendezVous,
            'patient' => $patient,
        ]);
    }

    #[Route('/deleteRdv/{id}', name: 'delete_rdv')]
    public function deleteRendezVous(ManagerRegistry $rm, int $id): Response
    {
        $entityManager = $rm->getManager();
        $rdv = $entityManager->getRepository(RendezVous::class)->find($id);

        if (!$rdv) {
            throw $this->createNotFoundException('Le rendez-vous n\'existe pas.');
        }

        // Récupérer l'ID du patient à partir du rendez-vous
        $patientId = $rdv->getPatient()->getId();

        $entityManager->remove($rdv);
        $entityManager->flush();

        $this->addFlash('success', 'Rendez-vous supprimé avec succès.');

        // Rediriger vers la liste des rendez-vous du patient
        return $this->redirectToRoute('listrdv', ['id' => $patientId]);
    }

    #[Route('/editRdv/{id}', name: 'edit_rdv')]
    public function editRendezVous(ManagerRegistry $rm, Request $req, int $id): Response
    {
        $entityManager = $rm->getManager();
        $rdv = $entityManager->getRepository(RendezVous::class)->find($id);
    
        if (!$rdv) {
            throw $this->createNotFoundException('Le rendez-vous n\'existe pas.');
        }
    
        $form = $this->createForm(RendezVousType::class, $rdv);
        $form->handleRequest($req);
    
        if ($form->isSubmitted() && $form->isValid()) {
            if ($rdv->getDate() < new \DateTime('today')) {
                $this->addFlash('error', 'Vous ne pouvez pas choisir une date antérieure à aujourd\'hui.');
                return $this->redirectToRoute('edit_rdv', ['id' => $id]);
            }
    
            $entityManager->flush();
    
            $this->addFlash('success', 'Votre rendez-vous a été modifié avec succès.');
    
            return $this->redirectToRoute('listrdv', ['id' => $rdv->getPatient()->getId()]);
        }
    
        return $this->render('rendez_vous/editrdv.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    

}
