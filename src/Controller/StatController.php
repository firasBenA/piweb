<?php
// src/Controller/StatController.php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Entity\User;
use App\Repository\RendezVousRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class StatController extends AbstractController
{
    #[Route('/stat', name: 'app_dashboard')]
public function index(Request $request, RendezVousRepository $rendezVousRepo, UserRepository $userRepo): Response
{
    // Récupérer toutes les spécialités disponibles
    $specialites = $userRepo->getDistinctSpecialites();

    // Si une spécialité est sélectionnée, afficher les médecins de cette spécialité
    $medecins = [];
    $statistiques = null;
    $specialite = $request->query->get('specialite', null);
    $medecinId = $request->query->get('medecin', null);

    if ($specialite) {
        $medecins = $userRepo->findMedecinsBySpecialite($specialite);
    }

    if ($medecinId) {
        $medecin = $userRepo->findMedecinById($medecinId);

        // Récupérer les statistiques du médecin
        $totalRendezVous = $rendezVousRepo->countByMedecin($medecin);
        $rendezVousApprouves = $rendezVousRepo->countByMedecinAndStatut($medecin, 'Approuvé');
        $rendezVousAnnules = $rendezVousRepo->countByMedecinAndStatut($medecin, 'Refusé');

        // Calcul du pourcentage des rendez-vous approuvés et refusés
        $pourcentageAnnules = ($totalRendezVous > 0) ? number_format(($rendezVousAnnules / $totalRendezVous) * 100, 2) : 0;
        $pourcentageApprouves = ($totalRendezVous > 0) ? number_format(($rendezVousApprouves / $totalRendezVous) * 100, 2) : 0;

        // Récupérer la répartition par sexe pour les rendez-vous approuvés
        $statistiquesSexe = $rendezVousRepo->countByMedecinAndSexeAndStatut($medecin, 'Approuvé');
        $totalApprouves = $statistiquesSexe['homme'] + $statistiquesSexe['femme'];
        $pourcentageHomme = ($totalApprouves > 0) ? ($statistiquesSexe['homme'] / $totalApprouves) * 100 : 0;
        $pourcentageFemme = ($totalApprouves > 0) ? ($statistiquesSexe['femme'] / $totalApprouves) * 100 : 0;

        // Récupérer la répartition par âge pour les rendez-vous approuvés
        $statistiquesAge1 = $rendezVousRepo->countByMedecinAndAgeAndStatut($medecin, 'Approuvé');
        $statistiquesAge = [
            '0-18' => $statistiquesAge1['0-18']['percentage'],
            '19-35' => $statistiquesAge1['19-35']['percentage'],
            '36-50' => $statistiquesAge1['36-50']['percentage'],
            '51-70' => $statistiquesAge1['51-70']['percentage'],
            '71+' => $statistiquesAge1['71+']['percentage']
        ];

        // Récupérer la répartition des rendez-vous par jour de la semaine
        $rendezVousParJour = $rendezVousRepo->countByMedecinAndJourSemaine($medecin);

// Vérifiez que la structure est correcte (0 => Dimanche, 1 => Lundi, ... 6 => Samedi)
if (count($rendezVousParJour) != 7) {
    // Vous pouvez corriger ou ajuster la structure des données si nécessaire
    $rendezVousParJour = array_fill(0, 7, 0); // Par défaut, assignez 0 à chaque jour
}


        // Organiser les statistiques
        $statistiques = [
            'medecin' => $medecin,
            'totalRendezVous' => $totalRendezVous,
            'rendezVousApprouves' => $rendezVousApprouves,
            'rendezVousAnnules' => $rendezVousAnnules,
            'pourcentageApprouves' => $pourcentageApprouves,
            'pourcentageAnnules' => $pourcentageAnnules,
            'pourcentageHomme' => $pourcentageHomme,
            'pourcentageFemme' => $pourcentageFemme,
            'statistiquesAge' => $statistiquesAge,
            'rendezVousParJour' => $rendezVousParJour // Ajouter cette ligne pour passer les données à la vue
        ];
    }

    // Retourner la vue avec les filtres et statistiques
    return $this->render('stat/stat.html.twig', [
        'specialites' => $specialites,
        'medecins' => $medecins,
        'statistiques' => $statistiques,
        'selectedSpecialite' => $specialite,
        'selectedMedecin' => $medecinId,
    ]);
}

}
