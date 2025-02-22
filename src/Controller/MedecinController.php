<?php

namespace App\Controller;

use App\Entity\Diagnostique;
use App\Entity\DossierMedical;
use App\Entity\Patient;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MedecinController extends AbstractController
{


    #[Route('medash/{id}', name: 'medecin_dashboard')]
    public function dashboard(User $user): Response
    {
        return $this->render('consultation/meddash.html.twig', [
            'medecin' => $user,
        ]);
    }

    #[Route('/infomed/{id}', name: 'infomed')]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        $medecin = $entityManager->getRepository(User::class)->find($id);

        if (!$medecin) {
            throw $this->createNotFoundException('Médecin non trouvé.');
        }

        return $this->render('consultation/infomed.html.twig', [
            'medecin' => $medecin,
        ]);
    }


    /*#[Route('/medecin/dashboard/{id}', name: 'medecinDashboard_page')]
    public function dashboard(int $id, EntityManagerInterface $entityManager): Response
    {
        // Fetch the doctor (medecin) by its ID
        $medecin = $entityManager->getRepository(Medecin::class)->find($id);

        if (!$medecin) {
            throw $this->createNotFoundException('Médecin non trouvé.');
        }

        // Fetch patients related to the doctor (medecin)
        $patients = $medecin->getPatients(); // Directly using the relation

        return $this->render('medecin/index.html.twig', [
            'medecin' => $medecin,
            'patients' => $patients,
        ]);
    }*/



    #[Route('/medecin/dossierMedicalByPatient/{id}', name: 'medecinDossierMedicalByPatient_page')]
    public function MedecinDossierMedical(int $id, EntityManagerInterface $entityManager): Response
    {
        // Fetch the patient by ID
        $patient = $entityManager->getRepository(Patient::class)->find($id);

        // Debugging - Log the patient object
        dump($patient);

        if (!$patient) {
            throw $this->createNotFoundException('No patient found with this ID.');
        }

        // Fetch the dossier medical linked to the patient
        $dossierMedical = $entityManager->getRepository(DossierMedical::class)->findOneBy(['patient' => $patient]);

        // Debugging - Log the dossierMedical object
        dump($dossierMedical);

        if (!$dossierMedical) {
            throw $this->createNotFoundException('No dossier medical found for this patient.');
        }

        // Fetch the diagnostiques related to the dossier medical
        $diagnostiques = $entityManager->getRepository(Diagnostique::class)->findBy(['dossierMedical' => $dossierMedical]);

        $prescriptions = $dossierMedical->getPrescriptions();
        $medecins = [];
        foreach ($prescriptions as $prescription) {
            if ($prescription->getMedecin() && !in_array($prescription->getMedecin(), $medecins, true)) {
                $medecins[] = $prescription->getMedecin();
            }
        }
        // Debugging - Log the diagnostiques
        dump($diagnostiques);

        return $this->render('medecin/dossierMedicalPatient.html.twig', [
            'dossierMedical' => $dossierMedical,
            'patient' => $patient, // Pass the patient to Twig
            'diagnostiques' => $diagnostiques, // Pass the diagnostiques to Twig
            'medecins' => $medecins,
        ]);
    }
    #[Route('/formMed', name: 'formMed_page')]
    public function formMed(): Response
    {
        return $this->render('medecin/form.html.twig', [
            'controller_name' => 'MedecinController',
        ]);
    }
}
