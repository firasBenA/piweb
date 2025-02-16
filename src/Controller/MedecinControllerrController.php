<?php

namespace App\Controller;

use App\Entity\Medecin;
use App\Form\MedecinType;
use App\Repository\MedecinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface; // Pour générer un nom de fichier unique


#[Route('/medecin')]
final class MedecinControllerrController extends AbstractController
{
    #[Route(name: 'app_medecin_controllerr_index', methods: ['GET'])]
    public function index(MedecinRepository $medecinRepository): Response
    {
        return $this->render('medecin_controllerr/index.html.twig', [
            'medecins' => $medecinRepository->findAll(),
        ]);
    }

    #[Route('/newM', name: 'app_medecin_controllerr_new', methods: ['GET', 'POST'])]
    public function new(Request $request, 
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher,
    SluggerInterface $slugger 
    ): Response
    {
        $medecin = new Medecin();
        $form = $this->createForm(MedecinType::class, $medecin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
             // Gérer le téléchargement du fichier
             $certificatFile = $form->get('certificat')->getData();

             if ($certificatFile) {
                 $originalFilename = pathinfo($certificatFile->getClientOriginalName(), PATHINFO_FILENAME);
                 $safeFilename = $slugger->slug($originalFilename);
                 $newFilename = $safeFilename . '-' . uniqid() . '.' . $certificatFile->guessExtension();
 
                 // Déplacer le fichier dans le répertoire de stockage
                 $certificatFile->move(
                     $this->getParameter('certificats_directory'), // Configurez ce paramètre dans services.yaml
                     $newFilename
                 );
 
                 // Enregistrer le chemin du fichier dans l'entité
                 $medecin->setCertificat($newFilename);
             }

             // Gérer le téléchargement de l'image de profil
        $imageDeProfilFile = $form->get('imageDeProfil')->getData();
        if ($imageDeProfilFile) {
            $originalFilename = pathinfo($imageDeProfilFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageDeProfilFile->guessExtension();
            $imageDeProfilFile->move(
                $this->getParameter('images_directory'), // Configurez ce paramètre dans services.yaml
                $newFilename
            );
            $medecin->setImageDeProfil($newFilename);
        }


           // Hachage du mot de passe
        $hashedPassword = $passwordHasher->hashPassword(
            $medecin,
            $form->get('motDePasse')->getData()
            );
        $medecin->setMotDePasse($hashedPassword);
           
            $entityManager->persist($medecin);
            $entityManager->flush();

            return $this->redirectToRoute('app_login', [], Response::HTTP_SEE_OTHER);
       
            
       
        }

        return $this->render('medecin_controllerr/new.html.twig', [
            'medecin' => $medecin,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_medecin_controllerr_show', methods: ['GET'])]
    public function show(Medecin $medecin): Response
    {
        return $this->render('medecin_controllerr/show.html.twig', [
            'medecin' => $medecin,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_medecin_controllerr_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Medecin $medecin, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MedecinType::class, $medecin);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           
                // Dump form errors to check what's wrong
                dump($form->getErrors(true)); // This will give you more information about the validation issue
            
            
            $entityManager->flush();

            return $this->redirectToRoute('app_medecin_controllerr_show', ['id' => $medecin->getId()], Response::HTTP_SEE_OTHER);

        }

        return $this->render('medecin_controllerr/edit.html.twig', [
            'medecin' => $medecin,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_medecin_controllerr_delete', methods: ['POST'])]
    public function delete(Request $request, Medecin $medecin, EntityManagerInterface $entityManager): Response
    {
        // Vérifier le jeton CSRF
        if ($this->isCsrfTokenValid('delete' . $medecin->getId(), $request->request->get('_token'))) {
            // Supprimer le médecin
            $entityManager->remove($medecin);
            $entityManager->flush();
            
            // Ajout d'un message flash pour informer l'utilisateur du succès
            $this->addFlash('success', 'Médecin supprimé avec succès.');
        }
    
        // Rediriger vers la liste des médecins après la suppression
        return $this->redirectToRoute('app_medecin_controllerr_index', [], Response::HTTP_SEE_OTHER);
    }
    
}
