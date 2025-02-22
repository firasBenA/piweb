<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Entity\Consultation;
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
            if ($rdv->getDate() < new \DateTime('today')) {
                // Modify the error message here
                $this->addFlash('error', 'La date choisie doit être aujourd\'hui ou à une date ultérieure. Veuillez sélectionner une date valide.');
                return $this->redirectToRoute('addrendezvous', ['id' => $id]);
            }

            $rdv->setPatient($patient);
            $rdv->setStatut('en attente');

            $entityManager->persist($rdv);
            $entityManager->flush();

            // 🔹 Création automatique de la consultation associée
            $consultation = new Consultation();
            $consultation->setRendezVous($rdv);
            $consultation->setPatient($patient);
            $consultation->setMedecin($rdv->getMedecin());
            $consultation->setDate($rdv->getDate());
            $consultation->setTypeConsultation($rdv->getTypeRdv());
            $consultation->setPrix(0); // Prix par défaut

            $entityManager->persist($consultation);
            $entityManager->flush();

            $this->addFlash('success', 'Votre rendez-vous a été enregistré avec succès. Une consultation a été créée.');

            return $this->redirectToRoute('listrdv', ['id' => $id]);
        }

        return $this->render('rendez_vous/addrdv.html.twig', [
            'form' => $form->createView(),
            'patient' => $patient,
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

        $rendezVous = $entityManager->getRepository(RendezVous::class)->findBy(['patient' => $patient]);

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

        $patientId = $rdv->getPatient()->getId();

        $entityManager->remove($rdv);
        $entityManager->flush();

        $this->addFlash('success', 'Rendez-vous supprimé avec succès.');

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

    #[Route('/details/{id}', name: 'detail_rdv')]
    public function details(ManagerRegistry $rm, int $id): Response
    {
        $entityManager = $rm->getManager();
        // Retrieve the appointment by its ID
        $rendezVous = $entityManager->getRepository(RendezVous::class)->find($id);

        // Check if the appointment exists
        if (!$rendezVous) {
            $this->addFlash('error', 'Rendez-vous non trouvé.');
            return $this->redirectToRoute('listrdv'); // Redirect to a list page
        }

        // Retrieve the doctor information
        $medecin = $rendezVous->getMedecin(); // Ensure getMedecin() is a valid method

        return $this->render('rendez_vous/detrdv.html.twig', [
            'date_rdv' => $rendezVous->getDate(), // Pass the DateTime object directly
            'type_rdv' => $rendezVous->getTypeRdv(),
            'cause' => $rendezVous->getCause(),
            'statut' => $rendezVous->getStatut(),
            'adresse' => $medecin->getAdresse(),
            'nom_medecin' => $medecin->getNom(),
            'prenom_medecin' => $medecin->getPrenom(),
            'specialite_medecin' => $medecin->getSpecialite(),
            'image_medecin' => $medecin->getImageDeProfil(),
        ]);
    }
}
