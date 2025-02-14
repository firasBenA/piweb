<?php
// src/Controller/DossierMedicalController.php

namespace App\Controller;

use App\Entity\Diagnostique;
use App\Entity\DossierMedical;
use App\Entity\Patient;
use App\Form\DossierMedicalType;
use App\Repository\DossierMedicalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DossierMedicalController extends AbstractController
{

    #[Route('/dossier-medical', name: 'app_dossier_medical_index')]
    public function index(DossierMedicalRepository $dossierMedicalRepository): Response
    {
        // Fetch all DossierMedical entries from the repository
        $dossierMedicals = $dossierMedicalRepository->findAll();

        return $this->render('dossier_medical/index_list.html.twig', [
            'dossier_medicals' => $dossierMedicals,
        ]);
    }

    #[Route('/dossier-medical/new', name: 'app_dossier_medical_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $dossierMedical = new DossierMedical();

        // Create the form
        $form = $this->createForm(DossierMedicalType::class, $dossierMedical);

        // Handle the form submission
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Persist the DossierMedical
            $entityManager->persist($dossierMedical);
            $entityManager->flush();

            // Redirect to the newly created dossier's page
            $this->addFlash('success', 'Dossier Medical created successfully');
            return $this->redirectToRoute('app_dossier_medical_show', ['id' => $dossierMedical->getId()]);
        }

        return $this->render('dossier_medical/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/dossierMedicalByPatient/{id}', name: 'dossierMedicalByPatient_page')]
    public function show(int $id, EntityManagerInterface $entityManager): Response
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

        // Debugging - Log the diagnostiques
        dump($diagnostiques);

        return $this->render('patient/dossierMedical.html.twig', [
            'dossierMedical' => $dossierMedical,
            'patient' => $patient, // Pass the patient to Twig
            'diagnostiques' => $diagnostiques, // Pass the diagnostiques to Twig
        ]);
    }
}
