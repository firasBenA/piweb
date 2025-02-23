<?php

namespace App\Controller;

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

class PatientController extends AbstractController
{
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

            $this->addFlash('success', 'Profil mis à jour avec succès.');
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

                $this->addFlash('success', 'Mot de passe mis à jour avec succès.');
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
            return $this->render('patient_dashboard.html.twig', [
                'form' => $form->createView(),
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle file uploads
            $imageProfilFile = $form->get('imageProfil')->getData();

            if ($imageProfilFile) {
                $imageProfilFileName = $this->uploadFile($imageProfilFile, $slugger, 'images_directory');
                $user->setImageProfil($imageProfilFileName);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('patient_dashboard');
        }

        return $this->render('patient_dashboard.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/patient/delete-profile', name: 'patient_delete_profile')]
    public function deleteProfile(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login2');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_logout');
    }

    private function uploadFile($file, SluggerInterface $slugger, $directoryParameter): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move(
                $this->getParameter($directoryParameter),
                $newFilename
            );
        } catch (FileException $e) {
            // handle exception if something happens during file upload
            throw new \Exception('File upload error: ' . $e->getMessage());
        }

        return $newFilename;
    }
}
