<?php

namespace App\Controller;

use App\Entity\DossierMedical;
use App\Form\DossierMedicalType;
use App\Repository\DossierMedicalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dossier-medical')]
class DossierMedical2Controller extends AbstractController
{
    #[Route('/', name: 'dossier_medical2_index', methods: ['GET'])]
    public function index(DossierMedicalRepository $dossierMedicalRepository): Response
    {
        return $this->render('dossier_medical2/index.html.twig', [
            'dossierMedicals' => $dossierMedicalRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'dossier_medical2_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $dossierMedical = new DossierMedical();
        $form = $this->createForm(DossierMedicalType::class, $dossierMedical);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($dossierMedical);
            $entityManager->flush();

            return $this->redirectToRoute('dossier_medical2_index');
        }

        return $this->render('dossier_medical2/new.html.twig', [
            'dossierMedical' => $dossierMedical,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'dossier_medical2_show', methods: ['GET'])]
    public function show(DossierMedical $dossierMedical): Response
    {
        return $this->render('dossier_medical2/show.html.twig', [
            'dossierMedical' => $dossierMedical,
        ]);
    }

    #[Route('/{id}/edit', name: 'dossier_medical2_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DossierMedical $dossierMedical, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DossierMedicalType::class, $dossierMedical);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('dossier_medical2_index');
        }

        return $this->render('dossier_medical2/edit.html.twig', [
            'dossierMedical' => $dossierMedical,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'dossier_medical2_delete', methods: ['POST'])]
    public function delete(Request $request, DossierMedical $dossierMedical, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$dossierMedical->getId(), $request->request->get('_token'))) {
            $entityManager->remove($dossierMedical);
            $entityManager->flush();
        }

        return $this->redirectToRoute('dossier_medical2_index');
    }
}
