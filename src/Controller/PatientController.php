<?php

namespace App\Controller;

use App\Entity\Patient;
use App\Form\PatientType;
use App\Repository\PatientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;



#[Route('/patient')]
final class PatientController extends AbstractController
{
    #[Route(name: 'app_patient_index', methods: ['GET'])]
    public function index(PatientRepository $patientRepository): Response
    {
        return $this->render('patient/index.html.twig', [
            'patients' => $patientRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_patient_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): Response {
        $patient = new Patient();
        $form = $this->createForm(PatientType::class, $patient);
        $form->handleRequest($request);
    
        if ($form->isSubmitted()) {
            // Récupérer les données du formulaire
            $motDePasse = $form->get('motDePasse')->getData();
            $confirmMotDePasse = $form->get('confirmMotDePasse')->getData();
    
            // Validation manuelle des mots de passe
            if ($motDePasse !== $confirmMotDePasse) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            } else {
                // Valider l'entité
                $errors = $validator->validate($patient);
    
                if (count($errors) === 0) {
                    // Hacher le mot de passe
                    $hashedPassword = $passwordHasher->hashPassword($patient, $motDePasse);
                    $patient->setMotDePasse($hashedPassword);
    
                    $entityManager->persist($patient);
                    $entityManager->flush();
    
                    $this->addFlash('success', 'Le patient a été créé avec succès.');
                    return $this->redirectToRoute('app_patient_index');
                } else {
                    // Afficher les erreurs de validation
                    foreach ($errors as $error) {
                        $this->addFlash('error', $error->getMessage());
                    }
                }
            }
        }
    
        return $this->render('patient/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
       /* $patient = new Patient();
        $form = $this->createForm(PatientType::class, $patient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($patient);
            $entityManager->flush();

            return $this->redirectToRoute('app_patient_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('patient/new.html.twig', [
            'patient' => $patient,
            'form' => $form,
        ]);
    }*/

    #[Route('/{id}', name: 'app_patient_show', methods: ['GET'])]
    public function show(Patient $patient): Response
    {
        return $this->render('patient/show.html.twig', [
            'patient' => $patient,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_patient_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Patient $patient, EntityManagerInterface $entityManager,UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(PatientType::class, $patient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $motDePasse = $form->get('motDePasse')->getData();
            if ($motDePasse) {
                // Hacher le mot de passe
                $hashedPassword = $passwordHasher->hashPassword($patient, $motDePasse);
                $patient->setMotDePasse($hashedPassword);
            }
           
            $entityManager->flush();

            return $this->redirectToRoute('app_patient_index', [], Response::HTTP_SEE_OTHER);
        }

        
        return $this->render('patient/edit.html.twig', [
            'patient' => $patient,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_patient_delete', methods: ['POST'])]
    public function delete(Request $request, Patient $patient, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$patient->getId(), $request->request->get('_token'))) {
            $entityManager->remove($patient);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_patient_index', [], Response::HTTP_SEE_OTHER);
    }
}
