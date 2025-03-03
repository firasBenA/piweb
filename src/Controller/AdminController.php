<?php

namespace App\Controller;

use App\Entity\Diagnostique;
use App\Entity\Prescription;
use App\Entity\Reclamation;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
class AdminController extends AbstractController
{
    public function index(Security $security): Response
    {
        // Get the logged-in user
        $user = $security->getUser();

        return $this->render('admin/index.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/admin/prescription', name: 'prescriptionAdmin')]
    public function prescription(Security $security, EntityManagerInterface $entityManager): Response
    {

        $user = $security->getUser();

        // Retrieve the 'prescriptions' related to the logged-in Medecin
        $diagnostiques = $entityManager->getRepository(Diagnostique::class)->findAll();
        $prescriptions = $entityManager->getRepository(Prescription::class)->findAll();


        return $this->render('admin/prescription.html.twig', [
            'user' => $user,
            'diagnostiques' => $diagnostiques,
            'prescriptions' => $prescriptions
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
    
}
