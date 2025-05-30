<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Entity\Consultation;
use App\Entity\Patient;
use App\Entity\Prescription;
use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\PrescriptionSearchType;
use App\Form\RendezVousType;
use App\Form\UpdateProfileFormType;
use App\Repository\PrescriptionRepository;
use App\Service\pdfService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

final class RendezVousController extends AbstractController
{
    #[Route('/addrendezvous', name: 'addrendezvous')]
    public function addRendezVous(ManagerRegistry $rm, Request $req, Security $security): Response
    {
        $entityManager = $rm->getManager();

        // Récupérer l'utilisateur connecté
        $user = $security->getUser();

        // Vérifier si l'utilisateur est un patient
        if (!$user || !in_array('ROLE_PATIENT', $user->getRoles())) {
            throw $this->createAccessDeniedException("Accès refusé. Seuls les patients peuvent prendre un rendez-vous.");
        }

        // Récupérer tous les utilisateurs ayant le rôle "ROLE_MEDECIN"
        $medecins = $entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_MEDECIN%')
            ->getQuery()
            ->getResult();

        $rdv = new RendezVous();
        $form = $this->createForm(RendezVousType::class, $rdv, [
            'medecins' => $medecins // Pass the filtered list of doctors (users with ROLE_MEDECIN)
        ]);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($rdv->getDate() === null) {
                $this->addFlash('error', 'La date ne peut pas être vide.');
                return $this->render('rendez_vous/addrdv.html.twig', [
                    'form' => $form->createView(),
                    'patient' => $user,
                ]);
            }
            $rdv->setUser($user);
            $rdv->setPatient($user);
            $rdv->setStatut('pending');

            // Enregistrer le rendez-vous
            $entityManager->persist($rdv);
            $entityManager->flush();

             $consultation = new Consultation();
            $consultation->setRendezVous($rdv); // Relier la consultation au rendez-vous
            $consultation->setPatient($user); // Relier la consultation au patient
            $consultation->setMedecin($rdv->getMedecin()); // Relier la consultation au médecin
            $consultation->setDate($rdv->getDate()); // Associer la même date de rendez-vous
            $consultation->setTypeConsultation('type à définir'); // Définir le type de consultation si nécessaire
    
            // Persist the consultation
            $entityManager->persist($consultation);
            $entityManager->flush();

            
        

            $this->addFlash('success', 'Votre rendez-vous a été enregistré avec succès.');

            return $this->redirectToRoute('listrdv');
        }

        return $this->render('rendez_vous/addrdv.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'patient' => $user,

        ]);
    }

#[Route('/listrdv', name: 'listrdv')]
public function listRendezVous(ManagerRegistry $rm, Security $security, Request $request): Response
{
    $entityManager = $rm->getManager();
    $user = $security->getUser();

    

    // Récupérer les paramètres de pagination et de recherche
    $page = $request->query->getInt('page', 1);
    $limit = 3;
    $offset = ($page - 1) * $limit;
    $search = $request->query->get('search'); // Paramètre de recherche

    $repository = $entityManager->getRepository(RendezVous::class);
    $queryBuilder = $repository->createQueryBuilder('r')
        ->leftJoin('r.medecin', 'm') // Joindre la table des médecins
        ->where('r.patient = :patient')
        ->setParameter('patient', $user);

    // Appliquer le filtre de recherche si un terme est fourni
    if ($search) {
        $queryBuilder->andWhere('m.nom LIKE :search OR m.prenom LIKE :search')
            ->setParameter('search', '%' . $search . '%');
    }

    // Pagination
    $queryBuilder->orderBy('r.date', 'DESC');
    $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($queryBuilder);
    $totalRendezVous = count($paginator);
    $totalPages = ceil($totalRendezVous / $limit);

    $rendezVous = $paginator
        ->getQuery()
        ->setFirstResult($offset)
        ->setMaxResults($limit)
        ->getResult();

    // Si c'est une requête AJAX, on retourne uniquement le contenu de la table
    if ($request->isXmlHttpRequest()) {
        return $this->render('rendez_vous/_table.html.twig', [
            'rendezVous' => $rendezVous,
            'currentPage' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    // Sinon, on retourne la page complète
    return $this->render('rendez_vous/listrdv.html.twig', [
        'rendezVous' => $rendezVous,
        'user' => $user,
        'currentPage' => $page,
        'totalPages' => $totalPages,
        'search' => $search, // Passer le terme de recherche au template
    ]);
}

   #[Route('/deleteRdv/{id}', name: 'delete_rdv')]
    public function deleteRendezVous(ManagerRegistry $rm, int $id): Response
    {
        $entityManager = $rm->getManager();
        $rdv = $entityManager->getRepository(RendezVous::class)->find($id);

        if (!$rdv) {
            throw $this->createNotFoundException('Le rendez-vous n\'existe pas.');
        }

        // Remove rendez-vous
        $entityManager->remove($rdv);
        $entityManager->flush();

        // Add success message
        $this->addFlash('success', 'Rendez-vous supprimé avec succès.');

        return $this->redirectToRoute('listrdv');
    }

    // Edit rendez-vous
    #[Route('/editRdv/{id}', name: 'edit_rdv')]
    public function editRendezVous(ManagerRegistry $rm, Request $req, int $id): Response
    {
        $entityManager = $rm->getManager();
        $rdv = $entityManager->getRepository(RendezVous::class)->find($id);

        if (!$rdv) {
            throw $this->createNotFoundException('Le rendez-vous n\'existe pas.');
        }

        // Set default values to avoid null errors during edit
        if ($rdv->getTypeRdv() === null) {
            $rdv->setTypeRdv('consultation');
        }

        if ($rdv->getCause() === null) {
            $rdv->setCause('Non spécifié');
        }

        if ($rdv->getDate() === null) {
            $rdv->setDate(new \DateTime());
        }

        $form = $this->createForm(RendezVousType::class, $rdv);
        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Votre rendez-vous a été modifié avec succès.');

            return $this->redirectToRoute('listrdv');
        }

        return $this->render('rendez_vous/editrdv.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    // For details of a rendez-vous
    #[Route('/details/{id}', name: 'detail_rdv')]
    public function details(ManagerRegistry $rm, int $id): Response
    {
        $entityManager = $rm->getManager();
        $rendezVous = $entityManager->getRepository(RendezVous::class)->find($id);

        if (!$rendezVous) {
            $this->addFlash('error', 'Rendez-vous non trouvé.');
            return $this->redirectToRoute('listrdv');
        }

        $medecin = $rendezVous->getMedecin();

        return $this->render('rendez_vous/detrdv.html.twig', [
            'date_rdv' => $rendezVous->getDate(),
            'type_rdv' => $rendezVous->getTypeRdv(),
            'cause' => $rendezVous->getCause(),
            'statut' => $rendezVous->getStatut(),
            'adresse' => $medecin->getAdresse(),
            'nom_medecin' => $medecin->getNom(),
            'prenom_medecin' => $medecin->getPrenom(),
            'specialite_medecin' => $medecin->getSpecialite(),
            'image_medecin' => $medecin->getImageProfil(),
        ]);
    }

    #[Route('patdash/{id}', name: 'patientDashboard')]
    public function dashboard(
        Request $request,
        Security $security,
        PrescriptionRepository $prescriptionRepository,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // Get the currently logged-in patient
        $patient = $security->getUser();
        $user = $this->getUser();

        $formUpdate = $this->createForm(UpdateProfileFormType::class, $user);
        $passwordForm = $this->createForm(ChangePasswordFormType::class);
        $formUpdate->handleRequest($request);
        $passwordForm->handleRequest($request);

        if ($formUpdate->isSubmitted() && $formUpdate->isValid()) {
            // Handle file uploads
            $imageProfilFile = $formUpdate->get('imageProfil')->getData();

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


        // Retrieve all prescriptions associated with the patient (default)
        $prescriptions = $prescriptionRepository->findBy(['patient' => $patient]);

        // Create search form
        $form = $this->createForm(PrescriptionSearchType::class);
        $form->handleRequest($request);

        // Initialize search results
        $searchPrescriptions = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $searchTerm = $form->get('search')->getData();

            if ($searchTerm) {
                $queryBuilder = $entityManager->getRepository(Prescription::class)->createQueryBuilder('p');
                $queryBuilder
                    ->where('p.titre LIKE :search')
                    ->andWhere('p.patient = :patient') // Ensure filtering by logged-in patient
                    ->setParameter('search', '%' . $searchTerm . '%')
                    ->setParameter('patient', $patient);

                $searchPrescriptions = $queryBuilder->getQuery()->getResult();
            }
        }

        return $this->render('consultation/patdash.html.twig', [
            'patient' => $patient,
            'prescriptions' => $prescriptions, // Default list
            'searchPrescriptions' => $searchPrescriptions,
            'form' => $form->createView(),
            'formUpdate' => $formUpdate->createView(),
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
            return $this->json([
                'status' => 'error',
                'errors' => $form->getErrors(true),
            ], 400);
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

            return $this->json([
                'status' => 'success',
                'message' => 'Profil mis à jour avec succès.',
            ]);
        }

        return $this->json([
            'status' => 'error',
            'message' => 'Une erreur s\'est produite.',
        ], 500);
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
