<?php

// src/Repository/RendezVousRepository.php
namespace App\Repository;

use App\Entity\RendezVous;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class RendezVousRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RendezVous::class);
    }

    /**
     * Compter les rendez-vous d'un médecin
     */
    public function countByMedecin($medecin)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.medecin = :medecin')
            ->setParameter('medecin', $medecin)
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compter les rendez-vous d'un médecin avec un statut particulier
     */
    public function countByMedecinAndStatut($medecin, $statut)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.medecin = :medecin')
            ->andWhere('r.statut = :statut')
            ->setParameter('medecin', $medecin)
            ->setParameter('statut', $statut)
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compter les rendez-vous d'un sexe donné et d'un statut particulier
     */
    public function countBySexeAndStatut(string $sexe, string $statut): int
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->join('r.patient', 'p')  // Joindre l'entité Patient (assurez-vous que la relation est bien définie)
            ->where('p.sexe = :sexe')
            ->andWhere('r.statut = :statut')
            ->setParameter('sexe', $sexe)
            ->setParameter('statut', $statut)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compter les rendez-vous approuvés par sexe pour un médecin donné
     */
    public function countByMedecinAndSexeAndStatut($medecin, string $statut): array
    {
        // Initialisation de la requête
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->join('r.patient', 'p')  // Jointure avec Patient pour obtenir le sexe
            ->where('r.medecin = :medecin')
            ->andWhere('r.statut = :statut')
            ->setParameter('medecin', $medecin)
            ->setParameter('statut', $statut);

        // Calcul du nombre de rendez-vous approuvés pour les hommes
        $qbHomme = clone $qb;
        $qbHomme->andWhere('p.sexe = :homme')
                ->setParameter('homme', 'Homme');
        $countHomme = (int)$qbHomme->getQuery()->getSingleScalarResult();

        // Calcul du nombre de rendez-vous approuvés pour les femmes
        $qbFemme = clone $qb;
        $qbFemme->andWhere('p.sexe = :femme')
                ->setParameter('femme', 'Femme');
        $countFemme = (int)$qbFemme->getQuery()->getSingleScalarResult();

        return [
            'homme' => $countHomme,
            'femme' => $countFemme,
        ];
    }


    public function countByMedecinAndAgeAndStatut($medecin, string $statut): array
    {
        $qb = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->join('r.patient', 'p')  // Jointure avec Patient pour récupérer l'âge
            ->where('r.medecin = :medecin')
            ->andWhere('r.statut = :statut')
            ->setParameter('medecin', $medecin)
            ->setParameter('statut', $statut);
    
        // Tranches d'âge à définir
        $tranches = [
            '0-18' => [0, 18],
            '19-35' => [19, 35],
            '36-50' => [36, 50],
            '51-70' => [51, 70],
            '71+' => [71, 200],
        ];
    
        // Récupérer le nombre total de rendez-vous pour ce médecin et statut
        $totalRendezVous = (int)$qb->getQuery()->getSingleScalarResult();
    
        $result = [];
        foreach ($tranches as $label => [$ageMin, $ageMax]) {
            $qbAge = clone $qb;
            $qbAge->andWhere('p.age BETWEEN :startAge AND :endAge')
                  ->setParameter('startAge', $ageMin)
                  ->setParameter('endAge', $ageMax);
    
            $count = (int)$qbAge->getQuery()->getSingleScalarResult();
    
            // Calcul du pourcentage
            $percentage = $totalRendezVous > 0 ? ($count / $totalRendezVous) * 100 : 0;
            
            $result[$label] = [
                'count' => $count,
                'percentage' => round($percentage, 2) // Arrondi à deux décimales
            ];
        }
    
        return $result;
    }
    
    public function countByMedecinAndJourSemaine($medecin): array
{
    $qb = $this->createQueryBuilder('r')
        ->select('r.date')
        ->where('r.medecin = :medecin')
        ->setParameter('medecin', $medecin);

    // Exécutez la requête pour obtenir toutes les dates
    $result = $qb->getQuery()->getResult();

    // Initialisation du tableau des rendez-vous par jour de la semaine (0 = dimanche, 6 = samedi)
    $rendezVousParJour = array_fill_keys(range(0, 6), 0);

    // Parcourez les résultats et comptez les rendez-vous par jour de la semaine
    foreach ($result as $row) {
        $date = $row['date'];
        $jourSemaine = (int)$date->format('w'); // 'w' est le jour de la semaine (0 = dimanche, 6 = samedi)
        $rendezVousParJour[$jourSemaine]++;
    }

    return $rendezVousParJour;
}
// RendezVousRepository.php

public function findByWithFilter($user, ?string $type, int $limit, int $offset)
{
    $qb = $this->createQueryBuilder('r')
        ->andWhere('r.patient = :user')
        ->setParameter('user', $user)
        ->orderBy('r.date', 'DESC')
        ->setMaxResults($limit)
        ->setFirstResult($offset);

    if ($type) {
        $qb->andWhere('r.typeRdv = :type')
           ->setParameter('type', $type);
    }

    return $qb->getQuery()->getResult();
}

public function countWithFilter($user, ?string $type): int
{
    $qb = $this->createQueryBuilder('r')
        ->select('count(r.id)')
        ->andWhere('r.patient = :user')
        ->setParameter('user', $user);

    if ($type) {
        $qb->andWhere('r.typeRdv = :type')
           ->setParameter('type', $type);
    }

    return $qb->getQuery()->getSingleScalarResult();
}

}