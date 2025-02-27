<?php

namespace App\Controller;

use App\Entity\DossierMedical;
use App\Entity\User;
use App\Form\RegistrationFormType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Set the roles for the user
            $selectedRole = $form->get('roles')->getData();
            $user->setRoles([$selectedRole]);

            // Handle file uploads
            $certificatFile = $form->get('certificat')->getData();
            $imageProfilFile = $form->get('imageProfil')->getData();

            if ($certificatFile) {
                $certificatFileName = $this->uploadFile($certificatFile, $slugger, 'certificats_directory');
                $user->setCertificat($certificatFileName);
            }

            if ($imageProfilFile) {
                $imageProfilFileName = $this->uploadFile($imageProfilFile, $slugger, 'images_directory');
                $user->setImageProfil($imageProfilFileName);
            }

            // Create a new DossierMedical for the user
            $dossierMedical = new DossierMedical();
            $dossierMedical->setUser($user); 
            $dossierMedical->setDatePrescription(new \DateTime());

            // Persist the user and the dossier
            $entityManager->persist($user);
            $entityManager->persist($dossierMedical);
            $entityManager->flush();

            // Redirect to login page after registration
            return $this->redirectToRoute('app_login2');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
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
            // handle exception if something happens during file upload
        }

        return $newFilename;
    }
}
