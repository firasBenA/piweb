<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Diagnostique;
use App\Entity\Evenement;
use App\Entity\Prescription;
use App\Entity\Reclamation;
use App\Entity\User;
use App\Repository\DiagnostiqueRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AdminController extends AbstractController
{

    #[Route('admin/prescription', name: 'prescriptionAdmin')]
    public function prescriptionDashboard(
        Security $security,
        EntityManagerInterface $entityManager,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $user = $security->getUser();
        $queryBuilder = $entityManager->getRepository(Diagnostique::class)->createQueryBuilder('d');

        $page = $request->query->getInt('page', 1);
        $diagnostiques = $paginator->paginate(
            $queryBuilder->getQuery(),
            $page,
            5
        );

        // Handle AJAX requests for pagination
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'results' => $this->renderView('admin/prescDiag/_diagnostics_table.html.twig', [
                    'diagnostiques' => $diagnostiques
                ]),
                'pagination' => $this->renderView('/admin/prescDiag/_pagination.html.twig', [
                    'pagination' => $diagnostiques
                ])
            ]);
        }

        $prescriptions = $entityManager->getRepository(Prescription::class)->findAll();

        return $this->render('admin/prescDiag/prescription.html.twig', [
            'user' => $user,
            'diagnostiques' => $diagnostiques,
            'prescriptions' => $prescriptions
        ]);
    }

    #[Route('/admin/prescription/search', name: 'search_diagnostique', methods: ['GET'])]
    public function searchDiagnostiques(Request $request, PaginatorInterface $paginator, EntityManagerInterface $em): JsonResponse
    {
        $searchTerm = $request->query->get('search', '');
        $queryBuilder = $em->getRepository(Diagnostique::class)->createQueryBuilder('d')
            ->where('d.nom LIKE :searchTerm OR d.description LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('d.nom', 'ASC');

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            5 
        );

        $diagnostiques = $pagination->getItems();

        $data = [];
        foreach ($diagnostiques as $diagnostique) {
            $data[] = [
                'id' => $diagnostique->getId(),
                'nom' => $diagnostique->getNom(),
                'description' => $diagnostique->getDescription(),
                'date' => $diagnostique->getDateDiagnostique()->format('Y-m-d'),
            ];
        }

        return new JsonResponse([
            'results' => $data,
            'pagination' => $this->renderView('admin/prescDiag/_pagination.html.twig', [
                'pagination' => $pagination
            ])
        ]);
    }



    // #[Route('/admin/prescription', name: 'prescriptionAdmin')]
    // public function prescription(
    //     Request $request,
    //     Security $security,
    //     EntityManagerInterface $entityManager,
    //     PaginatorInterface $paginator
    // ): Response {
    //     $user = $security->getUser();
    //     $queryBuilder = $entityManager->getRepository(Diagnostique::class)->createQueryBuilder('d');

    //     $page = $request->query->getInt('page', 1);
    //     $diagnostiques = $paginator->paginate(
    //         $queryBuilder->getQuery(),
    //         $page,
    //         5
    //     );

    //     // Return only the table if AJAX request (to prevent full page reload)
    //     if ($request->isXmlHttpRequest()) {
    //         return $this->render('admin/prescDiag/_diagnostics_table.html.twig', [
    //             'diagnostiques' => $diagnostiques
    //         ]);
    //     }

    //     $prescriptions = $entityManager->getRepository(Prescription::class)->findAll();

    //     return $this->render('admin/prescDiag/prescription.html.twig', [
    //         'user' => $user,
    //         'diagnostiques' => $diagnostiques,
    //         'prescriptions' => $prescriptions
    //     ]);
    // }

    // 

  
    #[Route('/admin/diagnostique/search', name: 'diagnostiqueAdmin_search', methods: ['GET'])]
    public function searchAdmin(Request $request, DiagnostiqueRepository $diagnostiqueRepository, LoggerInterface $logger, PaginatorInterface $paginator): JsonResponse
    {
        try {
            $searchTerm = $request->query->get('search', '');
            $page = $request->query->getInt('page', 1); // Get page number
            $limit = 5; // Number of results per page

            // Search query
            $queryBuilder = $diagnostiqueRepository->createQueryBuilder('d');

            if ($searchTerm) {
                $queryBuilder->where('d.nom LIKE :search')
                    ->setParameter('search', '%' . $searchTerm . '%');
            }

            // Paginate the results
            $pagination = $paginator->paginate(
                $queryBuilder, 
                $page, 
                $limit 
            );

            // Prepare the data to be sent as JSON
            $data = [];
            foreach ($pagination->getItems() as $diagnostique) {
                $data[] = [
                    'id' => $diagnostique->getId(),
                    'nom' => $diagnostique->getNom(),
                    'zoneCorps' => $diagnostique->getZoneCorps(),
                    'date' => $diagnostique->getDateDiagnostique()->format('Y-m-d'),
                    'medecin' => $diagnostique->getMedecin() ? $diagnostique->getMedecin()->getNom() : null,
                    'status' => $diagnostique->getStatus(),
                ];
            }

            // Manually calculate total pages
            $totalItems = $pagination->getTotalItemCount();
            $totalPages = ceil($totalItems / $limit);

            // Return paginated data along with pagination details
            return $this->json([
                'results' => $data,
                'totalPages' => $totalPages,  // Total pages calculated manually
                'currentPage' => $pagination->getCurrentPageNumber(),  // Current page number
            ]);
        } catch (\Exception $e) {
            $logger->error('Error in search: ' . $e->getMessage());
            return $this->json(['error' => 'An error occurred'], 500);
        }
    }


    #[Route('/diagnostique/search', name: 'diagnostique_search', methods: ['GET'])]
    public function search(Request $request, DiagnostiqueRepository $diagnostiqueRepository, LoggerInterface $logger, PaginatorInterface $paginator, Security $security): JsonResponse
    {
        try {
            $searchTerm = $request->query->get('search', '');
            $page = $request->query->getInt('page', 1); 
            $limit = 5; 

            $user = $security->getUser();

            if (!$user instanceof User || !method_exists($user, 'getId')) {
                throw $this->createAccessDeniedException('Access Denied. Medecin not found.');
            }

            $medecinId = $user->getId(); 

            $queryBuilder = $diagnostiqueRepository->createQueryBuilder('d')
                ->innerJoin('d.medecin', 'm')
                ->where('m.id = :medecinId')
                ->setParameter('medecinId', $medecinId);

            if ($searchTerm) {
                $queryBuilder->andWhere('d.nom LIKE :search')
                    ->setParameter('search', '%' . $searchTerm . '%');
            }

            $pagination = $paginator->paginate(
                $queryBuilder, 
                $page, 
                $limit 
            );

            // Prepare the data to be sent as JSON
            $data = [];
            foreach ($pagination->getItems() as $diagnostique) {
                $data[] = [
                    'id' => $diagnostique->getId(),
                    'nom' => $diagnostique->getNom(),
                    'zoneCorps' => $diagnostique->getZoneCorps(),
                    'date' => $diagnostique->getDateDiagnostique()->format('Y-m-d'),
                    'medecin' => $diagnostique->getMedecin() ? $diagnostique->getMedecin()->getNom() : null,
                    'status' => $diagnostique->getStatus(),
                ];
            }

            // Manually calculate total pages
            $totalItems = $pagination->getTotalItemCount();
            $totalPages = ceil($totalItems / $limit);

            // Return paginated data along with pagination details
            return $this->json([
                'results' => $data,
                'totalPages' => $totalPages,  
                'currentPage' => $pagination->getCurrentPageNumber(),  
            ]);
        } catch (\Exception $e) {
            $logger->error('Error in search: ' . $e->getMessage());
            return $this->json(['error' => 'An error occurred'], 500);
        }
    }






    #[Route('/admin/evenArtic', name: 'evenArticAdmin')]
    public function evenArtic(
        Security $security,
        EntityManagerInterface $entityManager,
        PaginatorInterface $paginator,
        Request $request
    ): Response {
        $user = $security->getUser();


        $evenementsQuery = $entityManager->getRepository(Evenement::class)->createQueryBuilder('e')->getQuery();
        $articlesQuery = $entityManager->getRepository(Article::class)->createQueryBuilder('a')->getQuery();

        $evenements = $paginator->paginate(
            $evenementsQuery,
            $request->query->getInt('page', 1),
            3
        );

        $articles = $paginator->paginate(
            $articlesQuery,
            $request->query->getInt('page', 1),
            3
        );

        return $this->render('admin/evenArtic.html.twig', [
            'user' => $user,
            'evenements' => $evenements,
            'articles' => $articles
        ]);
    }

    #[Route('/admin/reponseRec', name: 'reponseRecAdmin')]
    public function listereclamation(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 3; // Nombre de résultats par page
        $etat = $request->query->get('etat');

        // Création des critères de recherche (sans QueryBuilder)
        $criteria = [];
        if ($etat) {
            $criteria['etat'] = $etat;
        }

        // Récupération des réclamations selon l'état
        $reclamations = $entityManager->getRepository(Reclamation::class)
            ->findBy($criteria, ['date_debut' => 'DESC']);

        // Pagination sur le tableau de résultats
        $pagination = $paginator->paginate(
            $reclamations, // Tableau de résultats
            $page,
            $limit
        );

        // Gestion des requêtes AJAX
        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/_tabledash.html.twig', [
                'pagination' => $pagination,
            ]);
        }

        return $this->render('admin/reponseRec.html.twig', [
            'pagination' => $pagination,
        ]);
    }
    #[Route('/admin/export-excel', name: 'export_reclamations')]
    public function exportToExcel(EntityManagerInterface $entityManager): Response
    {
        // Récupérer toutes les réclamations et leurs réponses
        $reclamations = $entityManager->getRepository(Reclamation::class)->findAll();

        // Création d'un nouveau fichier Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Définition des en-têtes de colonnes
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Nom');
        $sheet->setCellValue('C1', 'Description');
        $sheet->setCellValue('D1', 'Date');
        $sheet->setCellValue('E1', 'État');
        $sheet->setCellValue('F1', 'Réponse');

        // Ajout des données
        $row = 2;
        foreach ($reclamations as $reclamation) {
            $sheet->setCellValue('A' . $row, $reclamation->getId());
            $sheet->setCellValue('B' . $row, $reclamation->getSujet());
            $sheet->setCellValue('C' . $row, $reclamation->getDescription());
            $sheet->setCellValue('D' . $row, $reclamation->getDateDebut()->format('d-m-Y'));
            $sheet->setCellValue('E' . $row, $reclamation->getEtat());

            // Vérifier si une réponse existe
            if ($reclamation->getEtat() === 'en_attente') {
                $reponse = 'Pas de réponse';
            } else {
                $reponse = $reclamation->getReponse() ? $reclamation->getReponse()->getContenu() : '';
            }
            $sheet->setCellValue('F' . $row, $reponse);


            $row++;
        }

        // Création d'une réponse HTTP avec téléchargement du fichier
        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        // Configuration des headers pour le téléchargement
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="reclamations.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
    #[Route('/', name: 'admin_dashboard')]
    public function index(Security $security, UserRepository $userRepository): Response
    {
        // Get the logged-in user
        $user = $security->getUser();

        // Get the list of medecins and patients
        $medecins = $userRepository->findByRoles('ROLE_MEDECIN');
        $patients = $userRepository->findByRoles('ROLE_PATIENT');

        // Debug: Check the data fetched
        dump($medecins, $patients);

        return $this->render('admin/index.html.twig', [
            'user' => $user,
            'medecins' => $medecins,
            'patients' => $patients,
        ]);
    }


    #[Route('/users', name: 'admin_users', methods: ['GET'])]
    public function listUsers(UserRepository $userRepository): Response
    {
        // Get the list of medecins and patients
        $medecins = $userRepository->findByRoles('ROLE_MEDECIN');
        $patients = $userRepository->findByRoles('ROLE_PATIENT');

        // Render the Twig template
        return $this->render('admin/index.html.twig', [
            'medecins' => $medecins,
            'patients' => $patients,
        ]);
    }

    #[Route('/admin/users/{id}/block', name: 'admin_block_user', methods: ['POST'])]
    public function blockUser(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer le rôle actuel de l'utilisateur
        $currentRoles = $user->getRoles();

        // Si l'utilisateur n'est pas déjà bloqué, stocker son rôle actuel dans un rôle personnalisé
        if (!in_array('BLOCKED', $currentRoles)) {
            // Ajouter un rôle personnalisé pour stocker le rôle d'origine
            $originalRole = $currentRoles[0]; // Rôle d'origine (MEDECIN ou PATIENT)
            $user->setRoles(['BLOCKED', 'ORIGINAL_ROLE_' . $originalRole]);
        }

        // Bloquer l'utilisateur
        $entityManager->flush();

        return $this->json([
            'status' => 'User blocked successfully',
            'newRole' => 'BLOCKED',
            'userId' => $user->getId(),
        ]);
    }

    #[Route('/admin/users/{id}/unblock', name: 'admin_unblock_user', methods: ['POST'])]
    public function unblockUser(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer tous les rôles de l'utilisateur
        $currentRoles = $user->getRoles();

        // Trouver le rôle d'origine (s'il existe)
        $originalRole = null;
        foreach ($currentRoles as $role) {
            if (strpos($role, 'ORIGINAL_ROLE_') === 0) {
                $originalRole = str_replace('ORIGINAL_ROLE_', '', $role);
                break;
            }
        }

        // Si aucun rôle d'origine n'est trouvé, attribuer un rôle par défaut (PATIENT ou MEDECIN)
        if (!$originalRole) {
            $originalRole = 'ROLE_PATIENT'; // ou une autre logique pour déterminer le rôle par défaut
        }

        // Débloquer l'utilisateur et lui redonner son rôle d'origine
        $user->setRoles([$originalRole]);
        $entityManager->flush();

        return $this->json([
            'status' => 'User unblocked successfully',
            'newRole' => $originalRole,
            'userId' => $user->getId(),
        ]);
    }
}
