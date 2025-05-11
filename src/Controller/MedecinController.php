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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[IsGranted('ROLE_MEDECIN')]
class MedecinController extends AbstractController
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

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
            $this->addFlash('error', 'Vous devez être connecté pour modifier votre profil.');
            return $this->redirectToRoute('app_login2');
        }

        try {
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

            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur s\'est produite lors de la mise à jour du profil : ' . $e->getMessage());
        }

        return $this->redirectToRoute('medecin_dashboard');
    }

    #[Route('/medecin/delete-profile', name: 'medecin_delete_profile')]
    public function deleteProfile(EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour supprimer votre compte.');
            return $this->redirectToRoute('app_login2');
        }

        try {
            $entityManager->remove($user);
            $entityManager->flush();

            // Invalidate the session
            $session = $request->getSession();
            $session->invalidate();

            // Clear the security token to log out the user
            $this->tokenStorage->setToken(null);

            $this->addFlash('success', 'Votre compte a été supprimé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur s\'est produite lors de la suppression du compte : ' . $e->getMessage());
            return $this->redirectToRoute('medecin_dashboard');
        }

        return $this->redirectToRoute('app_login2');
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
            throw new \Exception('Erreur lors du téléchargement du fichier : ' . $e->getMessage());
        }

        return $newFilename;
    }
}
