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
        $limit = 4; // Nombre de résultats par page
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
    
}
