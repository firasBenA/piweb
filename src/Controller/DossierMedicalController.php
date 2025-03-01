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
        $dossierMedicals = $dossierMedicalRepository->findAll();

        return $this->render('dossier_medical/index_list.html.twig', [
            'dossier_medicals' => $dossierMedicals,
        ]);
    }

    #[Route('/dossier-medical/new', name: 'app_dossier_medical_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $dossierMedical = new DossierMedical();

        $form = $this->createForm(DossierMedicalType::class, $dossierMedical);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($dossierMedical);
            $entityManager->flush();

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
        $patient = $entityManager->getRepository(Patient::class)->find($id);

        dump($patient);

        if (!$patient) {
            throw $this->createNotFoundException('No patient found with this ID.');
        }

        $dossierMedical = $entityManager->getRepository(DossierMedical::class)->findOneBy(['patient' => $patient]);

        dump($dossierMedical);

        if (!$dossierMedical) {
            throw $this->createNotFoundException('No dossier medical found for this patient.');
        }

        $diagnostiques = $entityManager->getRepository(Diagnostique::class)->findBy(['dossierMedical' => $dossierMedical]);

        $prescriptions = $dossierMedical->getPrescriptions();
        $medecins = [];
        foreach ($prescriptions as $prescription) {
            if ($prescription->getMedecin() && !in_array($prescription->getMedecin(), $medecins, true)) {
                $medecins[] = $prescription->getMedecin();
            }
        }

        return $this->render('patient/dossierMedical.html.twig', [
            'dossierMedical' => $dossierMedical,
            'patient' => $patient, 
            'diagnostiques' => $diagnostiques, 
            'medecins' => $medecins,
        ]);
    }

}
