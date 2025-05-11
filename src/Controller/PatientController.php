<?php

namespace App\Controller;
/*
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

        */

use App\Entity\Diagnostique;
use App\Entity\DossierMedical;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Form\RegistrationFormType;
use App\Form\UpdateProfileFormType;
use App\Form\ChangePasswordFormType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


#[IsGranted('ROLE_PATIENT')]
class PatientController extends AbstractController
{
 private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }
    
    #[Route('/patient', name: 'patient_dashboard')]
    public function index(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, UserPasswordHasherInterface $passwordHasher): Response
    {

        $user = $this->getUser();
        $form = $this->createForm(UpdateProfileFormType::class, $user);
        $passwordForm = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);
        $passwordForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file uploads
            $imageProfilFile = $form->get('imageProfil')->getData();

            if ($imageProfilFile) {
                $imageProfilFileName = $this->uploadFile($imageProfilFile, $slugger, 'images_directory');
                $user->setImageProfil($imageProfilFileName);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
            return $this->redirectToRoute('patient_dashboard');
        }

        if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $oldPassword = $passwordForm->get('oldPassword')->getData();
            $newPassword = $passwordForm->get('newPassword')->getData();
            $confirmPassword = $passwordForm->get('confirmPassword')->getData();

            if ($newPassword !== $confirmPassword) {
                $this->addFlash('error', 'Les nouveaux mots de passe ne correspondent pas.');
            } elseif (!$passwordHasher->isPasswordValid($user, $oldPassword)) {
                $this->addFlash('error', 'L\'ancien mot de passe est incorrect.');
            } else {
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a été mis à jour avec succès.');
                return $this->redirectToRoute('patient_dashboard');
            }
        }

        return $this->render('patient_dashboard.html.twig', [
            'form' => $form->createView(),
            'passwordForm' => $passwordForm->createView(),
        ]);
    }

    #[Route('/patient/update-profile', name: 'patient_update_profile', methods: ['POST'])]
public function updateProfile(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $user = $this->getUser();
    if (!$user) {
        return $this->redirectToRoute('app_login2');
    }

    $form = $this->createForm(UpdateProfileFormType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && !$form->isValid()) {
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'error',
                'errors' => $form->getErrors(true),
            ], 400);
        } else {
            // Handle regular form submit
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
            return $this->redirectToRoute('patient_dashboarde');
        }
    }

    if ($form->isSubmitted() && $form->isValid()) {
        // Handle image upload
        $imageProfilFile = $form->get('imageProfil')->getData();
        if ($imageProfilFile) {
            $imageProfilFileName = $this->uploadFile($imageProfilFile, $slugger, 'images_directory');
            $user->setImageProfil($imageProfilFileName);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'status' => 'success',
                'message' => 'Votre profil a été mis à jour avec succès.',
            ]);
        } else {
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
            return $this->redirectToRoute('patient_dashboarde');
        }
    }

    return $this->json([
        'status' => 'error',
        'message' => 'Une erreur s\'est produite.',
    ], 500);
}

    #[Route('/patient/delete-profile', name: 'patient_delete_profile')]
    public function deleteProfile(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login2');
        }

        // Delete the user
        $entityManager->remove($user);
        $entityManager->flush();

        // Invalidate the session
        $session = $request->getSession();
        $session->invalidate();

        // Clear the security token to log out the user
        $this->tokenStorage->setToken(null);

        // Add success flash message
        $this->addFlash('success', 'Votre compte a été supprimé avec succès.');

        return $this->redirectToRoute('app_login2'); // Redirect to login instead of logout
    }

    

    private function uploadFile($file, SluggerInterface $slugger, $directoryParameter): string
{
    $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    $safeFilename = $slugger->slug($originalFilename);
    $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

    try {
        $file->move(
            $this->getParameter($directoryParameter),
            $newFilename
        );
    } catch (FileException $e) {
        throw new \Exception('File upload error: ' . $e->getMessage());
    }

    return $newFilename;
}
}