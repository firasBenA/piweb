<?php
// src/Controller/DossierMedicalController.php

namespace App\Controller;

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

    #[Route('/dossier-medical/{id}', name: 'app_dossier_medical_show')]
    public function show(DossierMedical $dossierMedical): Response
    {
        return $this->render('dossier_medical/index.html.twig', [
            'dossier_medical' => $dossierMedical,
        ]);
    }
}

