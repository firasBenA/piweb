<?php

namespace App\Controller;

use App\Entity\Diagnostique;
use App\Entity\Patient;
use App\Entity\DossierMedical;
use App\Entity\Prescription;
use App\Form\PatientType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/patient')]
class PatientController extends AbstractController
{
    #[Route('/new', name: 'patient_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $patient = new Patient(); // Create new patient

        // Create a new DossierMedical entity and set the prescription date
        $dossierMedical = new DossierMedical();
        $dossierMedical->setDatePrescription(new \DateTime()); // Set current date as prescription date

        // Associate the DossierMedical with the Patient
        $patient->setDossierMedical($dossierMedical); // Link the DossierMedical to the Patient

        // Associate the Patient with the DossierMedical
        $dossierMedical->setPatient($patient); // Set the Patient on the DossierMedical

        // Create and handle the form for the Patient entity
        $form = $this->createForm(PatientType::class, $patient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the patient and its DossierMedical
            $entityManager->persist($patient); // This will also persist the DossierMedical because of the cascade
            $entityManager->flush(); // Commit changes to the database

            // Redirect to patient index page or any other page
            return $this->redirectToRoute('patient_index', [], Response::HTTP_SEE_OTHER);
        }

        // Render the form view
        return $this->render('patient/new.html.twig', [
            'patient' => $patient,
            'form' => $form,
        ]);
    }

    #[Route('/dashboard/{id}', name: 'patientDashboard_page')]
    public function dashboard(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer le patient
        $patient = $entityManager->getRepository(Patient::class)->find($id);

        if (!$patient) {
            throw $this->createNotFoundException('Patient non trouvé.');
        }

        // Récupérer le dossier médical du patient
        $dossierMedical = $patient->getDossierMedical();

        if (!$dossierMedical) {
            throw $this->createNotFoundException('Dossier médical non trouvé pour ce patient.');
        }

        // Récupérer les prescriptions associées
        $prescriptions = $dossierMedical->getPrescriptions();

        $diagnostiques = $entityManager->getRepository(Diagnostique::class)->findBy(['dossierMedical' => $dossierMedical]);

        // Extraire les médecins liés aux prescriptions
        $medecins = [];
        foreach ($prescriptions as $prescription) {
            if ($prescription->getMedecin() && !in_array($prescription->getMedecin(), $medecins, true)) {
                $medecins[] = $prescription->getMedecin();
            }
        }

        return $this->render('patient/dossierMedical.html.twig', [
            'patient' => $patient,
            'dossierMedical' => $dossierMedical,
            'prescriptions' => $prescriptions,
            'medecins' => $medecins,
            'diagnostiques' => $diagnostiques,
        ]);
    }


    #[Route('/index', name: 'patient_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $patients = $entityManager->getRepository(Patient::class)->findAll();

        return $this->render('patient/index.html.twig', [
            'patients' => $patients,
        ]);
    }
}
