<?php

namespace App\Controller;

use App\Entity\Diagnostique;
use App\Entity\Patient;
use App\Entity\DossierMedical;
use App\Entity\User;
use App\Form\PatientType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


#[Route('/patient')]
class PatientController extends AbstractController
{
    #[Route('/new', name: 'patient_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $patient = new Patient(); // Create new patient

        $dossierMedical = new DossierMedical();
        $dossierMedical->setDatePrescription(new \DateTime());

        $patient->setDossierMedical($dossierMedical);

        $dossierMedical->setPatient($patient);

        $form = $this->createForm(PatientType::class, $patient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($patient);
            $entityManager->flush();

            return $this->redirectToRoute('patient_index', [], Response::HTTP_SEE_OTHER);
        }

        // Render the form view
        return $this->render('patient/new.html.twig', [
            'patient' => $patient,
            'form' => $form,
        ]);
    }

    #[Route('/dashboard/{id}', name: 'patientDashboard_page')]
    public function dashboard(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, int $id): Response
    {
        $token = $tokenStorage->getToken();
        $user = $token?->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You are not logged in.');
        }

        // Retrieve the patient associated with the logged-in user
        $patient = $entityManager->getRepository(Patient::class)->findOneBy(['user' => $user]);

        if (!$patient) {
            throw $this->createNotFoundException('Patient not found for this user.');
        }

        // Retrieve the dossierMedical by ID and ensure it belongs to the patient
        $dossierMedical = $entityManager->getRepository(DossierMedical::class)->findOneBy([
            'id' => $id,
            'patient' => $patient
        ]);

        if (!$dossierMedical) {
            throw $this->createNotFoundException('Medical record not found for this patient.');
        }

        // Retrieve related data
        $prescriptions = $dossierMedical->getPrescriptions();
        $diagnostiques = $entityManager->getRepository(Diagnostique::class)->findBy([
            'dossierMedical' => $dossierMedical
        ]);

        // Collect medecins from prescriptions (avoid duplicates)
        $medecins = [];
        foreach ($prescriptions as $prescription) {
            $medecin = $prescription->getMedecin();
            if ($medecin && !in_array($medecin, $medecins, true)) {
                $medecins[] = $medecin;
            }
        }

        // Pass `dossierMedicalId` explicitly
        return $this->render('patient/dossierMedical.html.twig', [
            'patient' => $patient,
            'dossierMedical' => $dossierMedical,
            'prescriptions' => $prescriptions,
            'medecins' => $medecins,
            'diagnostiques' => $diagnostiques,
            'dossierMedicalId' => $dossierMedical->getId(), // ✅ Pass this explicitly
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
