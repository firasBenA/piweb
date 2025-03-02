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

use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('MEDECIN')]
class MedecinController extends AbstractController
{
    #[Route('/medecin', name: 'medecin_dashboard')]
    public function index(): Response
    {
        return $this->render('medecin_dashboard.html.twig');
    }

    #[Route('/medecin/update-profile', name: 'medecin_update_profile', methods: ['POST'])]
    public function updateProfile(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login2');
        }

        $user->setNom($request->request->get('nom'));
        $user->setPrenom($request->request->get('prenom'));
        $user->setEmail($request->request->get('email'));
        $user->setTelephone($request->request->get('telephone'));
        $user->setAdresse($request->request->get('adresse'));
        $user->setAge($request->request->get('age'));
        $user->setSexe($request->request->get('sexe'));
        $user->setSpecialite($request->request->get('specialite'));

        // Handle file uploads
        $certificatFile = $request->files->get('certificat');
        $imageProfilFile = $request->files->get('imageProfil');

        if ($certificatFile) {
            $certificatFileName = $this->uploadFile($certificatFile, $slugger, 'certificats_directory');
            $user->setCertificat($certificatFileName);
        }

        if ($imageProfilFile) {
            $imageProfilFileName = $this->uploadFile($imageProfilFile, $slugger, 'images_directory');
            $user->setImageProfil($imageProfilFileName);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('medecin_dashboard');
    }

    #[Route('/medecin/delete-profile', name: 'medecin_delete_profile')]
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
