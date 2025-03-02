<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Diagnostique;
use App\Entity\Evenement;
use App\Entity\Prescription;
use App\Repository\DiagnostiqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(Security $security): Response
    {
        // Get the logged-in user
        $user = $security->getUser();

        return $this->render('admin/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('admin/prescription', name: 'prescriptionAdmin')]
    public function prescriptionDashboard(
        Security $security,
        EntityManagerInterface $entityManager,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        // Fetch the currently authenticated user (Medecin)
        $user = $security->getUser();
        $queryBuilder = $entityManager->getRepository(Diagnostique::class)->createQueryBuilder('d');

        // Get the current page number (default: 1)
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

        // Paginate the results
        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            5 // 10 results per page
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
    #[Route('/diagnostique/search', name: 'diagnostique_search', methods: ['GET'])]
    public function search(Request $request, DiagnostiqueRepository $diagnostiqueRepository, LoggerInterface $logger, PaginatorInterface $paginator): JsonResponse
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
                $queryBuilder, // Query builder
                $page, // Current page number
                $limit // Number of results per page
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





    #[Route('/admin/evenArtic', name: 'evenArticAdmin')]
    public function evenArtic(Security $security, EntityManagerInterface $entityManager): Response
    {

        $user = $security->getUser();

        // Retrieve the 'prescriptions' related to the logged-in Medecin
        $evenements = $entityManager->getRepository(Evenement::class)->findAll();
        $articles = $entityManager->getRepository(Article::class)->findAll();


        return $this->render('admin/evenArtic.html.twig', [
            'user' => $user,
            'evenements' => $evenements,
            'articles' => $articles
        ]);
    }
}
