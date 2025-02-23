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

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            return $this->redirectToRoute('app_login'); // Assurez-vous que 'app_login' existe
        }

        // Mise à jour des informations de l'utilisateur
        $user->setNom($request->request->get('nom'));
        $user->setPrenom($request->request->get('prenom'));
        $user->setEmail($request->request->get('email'));
        $user->setTelephone($request->request->get('telephone'));
        $user->setAdresse($request->request->get('adresse'));
        $user->setAge($request->request->get('age'));
        $user->setSexe($request->request->get('sexe'));
        $user->setSpecialite($request->request->get('specialite'));

        // Gestion des fichiers téléchargés
        $certificatFile = $request->files->get('certificat');
        $imageProfilFile = $request->files->get('imageProfil');

        // Télécharger et enregistrer le certificat si présent
        if ($certificatFile) {
            $certificatFileName = $this->uploadFile($certificatFile, $slugger, 'certificats_directory');
            $user->setCertificat($certificatFileName);
        }

        // Télécharger et enregistrer l'image de profil si présente
        if ($imageProfilFile) {
            $imageProfilFileName = $this->uploadFile($imageProfilFile, $slugger, 'images_directory');
            $user->setImageProfil($imageProfilFileName);
        }

        // Sauvegarder les changements dans la base de données
        $entityManager->persist($user);
        $entityManager->flush();

        // Redirection vers le tableau de bord
        return $this->redirectToRoute('medecin_dashboard');
    }

    #[Route('/medecin/delete-profile', name: 'medecin_delete_profile')]
    public function deleteProfile(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            return $this->redirectToRoute('app_login'); // Assurez-vous que 'app_login' existe
        }

        // Supprimer l'utilisateur de la base de données
        $entityManager->remove($user);
        $entityManager->flush();

        // Déconnexion de l'utilisateur après la suppression
        return $this->redirectToRoute('app_logout'); // Assurez-vous que 'app_logout' existe
    }

    private function uploadFile($file, SluggerInterface $slugger, $directoryParameter): string
    {
        // Extraire le nom original du fichier
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        // Générer un nom de fichier sécurisé
        $safeFilename = $slugger->slug($originalFilename);
        // Créer un nouveau nom de fichier unique
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        try {
            // Déplacer le fichier téléchargé dans le répertoire spécifié
            $file->move(
                $this->getParameter($directoryParameter),
                $newFilename
            );
        } catch (FileException $e) {
            // Gestion des erreurs pendant l'upload du fichier
            throw new \Exception('Erreur lors du téléchargement du fichier : ' . $e->getMessage());
        }

        // Retourner le nom du fichier téléchargé
        return $newFilename;
    }
}
