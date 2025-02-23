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
        if ($rdv->getDate() === null) {
            $this->addFlash('error', 'La date ne peut pas être vide.');
            return $this->render('rendez_vous/addrdv.html.twig', [
                'form' => $form->createView(),
                'patient' => $patient,
            ]);
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

        // Création de la consultation
        $consultation = new Consultation();
        $consultation->setRendezVous($rdv);
        $consultation->setPatient($patient);
        $consultation->setMedecin($rdv->getMedecin());
        $consultation->setDate($rdv->getDate());
        $consultation->setTypeConsultation($rdv->getTypeRdv());
        $consultation->setPrix(0);

            $entityManager->persist($rdv);
            $entityManager->flush();

        $this->addFlash('success', 'Votre rendez-vous a été enregistré avec succès.');

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
    
        // ✅ Vérification et affectation des valeurs par défaut pour éviter les erreurs "null"
        if ($rdv->getTypeRdv() === null) {
            $rdv->setTypeRdv('consultation'); // Valeur par défaut
        }
    
        if ($rdv->getCause() === null) {
            $rdv->setCause('Non spécifié'); // Valeur par défaut
        }
    
        if ($rdv->getDate() === null) {
            $rdv->setDate(new \DateTime()); // Date actuelle par défaut
        }
    
        $form = $this->createForm(RendezVousType::class, $rdv);
        $form->handleRequest($req);
    
        if ($form->isSubmitted() && $form->isValid()) {
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
        $rendezVous = $entityManager->getRepository(RendezVous::class)->find($id);

        if (!$rendezVous) {
            $this->addFlash('error', 'Rendez-vous non trouvé.');
            return $this->redirectToRoute('listrdv');
        }

        $medecin = $rendezVous->getMedecin();

        return $this->render('rendez_vous/detrdv.html.twig', [
            'date_rdv' => $rendezVous->getDate(),
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



    #[Route('patdash/{id}', name: 'patientDashboard')]
    public function dashboard(Patient $patient): Response
    {
        return $this->render('consultation/patdash.html.twig', [
            'patient' => $patient,
        ]);
    }
}
