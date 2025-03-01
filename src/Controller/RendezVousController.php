<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Entity\Consultation;

use App\Entity\User;
use App\Repository\ConsultationRepository;
use App\Form\RendezVousType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;

final class RendezVousController extends AbstractController
{
    // Add rendez-vous without ID in the URL
    #[Route('/addrendezvous', name: 'addrendezvous')]
    public function addRendezVous(ManagerRegistry $rm, Request $req, Security $security, ConsultationRepository $consultationRepository): Response
    {
        $entityManager = $rm->getManager();
    
        // Récupérer l'utilisateur actuellement connecté
        $user = $security->getUser();
    
        // Vérifier si l'utilisateur est un patient
        if (!$user instanceof User || !in_array('PATIENT', $user->getRoles())) {
            throw $this->createAccessDeniedException('Vous devez être connecté en tant que patient pour prendre un rendez-vous.');
        }
    
        // Utiliser l'utilisateur comme patient
        $patient = $user;
    
        // Création du rendez-vous
        $rdv = new RendezVous();
        $form = $this->createForm(RendezVousType::class, $rdv);
        $form->handleRequest($req);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Assurez-vous que le patient et le médecin sont définis
            $rdv->setPatient($user);
            $rdv->setMedecin($form->get('medecin')->getData());
    
            // Définir le statut par défaut si ce n'est pas déjà fait
            if ($rdv->getStatut() === null) {
                $rdv->setStatut('en attente'); // Statut par défaut
            }
    
            // Validation de la date (optionnelle)
            if ($rdv->getDate() < new \DateTime('today')) {
                $this->addFlash('error', 'La date choisie doit être aujourd\'hui ou à une date ultérieure.');
                return $this->redirectToRoute('addrendezvous');
            }
    
            // Persist the rendez-vous
            $entityManager->persist($rdv);
            $entityManager->flush();
    
            // Créer la consultation après le rendez-vous
            $consultation = new Consultation();
            $consultation->setRendezVous($rdv); // Relier la consultation au rendez-vous
            $consultation->setPatient($patient); // Relier la consultation au patient
            $consultation->setMedecin($rdv->getMedecin()); // Relier la consultation au médecin
            $consultation->setDate($rdv->getDate()); // Associer la même date de rendez-vous
            $consultation->setTypeConsultation('type à définir'); // Définir le type de consultation si nécessaire
            $consultation->setPrix(0); // Mettre à 0 ou définir un prix
    
            // Persist the consultation
            $entityManager->persist($consultation);
            $entityManager->flush();
    
            $this->addFlash('success', 'Votre rendez-vous a été enregistré avec succès, et une consultation a été créée pour le médecin.');
            return $this->redirectToRoute('listrdv');
        }
    
        // Si le formulaire n'est pas soumis ou invalide, on affiche à nouveau le formulaire
        return $this->render('rendez_vous/addrdv.html.twig', [
            'form' => $form->createView(),
            'patient' => $user,
        ]);
    }
    
    

    // List rendez-vous for the logged-in patient
// ...

#[Route('/listrdv', name: 'listrdv')]
public function listRendezVous(ManagerRegistry $rm, Security $security, Request $request): Response
{
    $entityManager = $rm->getManager();
    $user = $security->getUser();

    if (!in_array('PATIENT', $user->getRoles())) {
        throw $this->createAccessDeniedException('Seuls les patients peuvent prendre un rendez-vous.');
    }

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
    // Delete rendez-vous
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
    
    #[Route('/patdash', name: 'patient_dashboard1')]
    public function dashboard(ManagerRegistry $rm, Security $security): Response
    {
        $patient = $security->getUser(); // Get the logged-in patient

        
        if (!$patient instanceof User || !in_array('PATIENT', $patient->getRoles())) {
            throw $this->createAccessDeniedException('Accès refusé : Seuls les patients peuvent accéder à ce tableau de bord.');
        }

        $entityManager = $rm->getManager();
        $rendezVous = $entityManager->getRepository(RendezVous::class)->findBy(['patient' => $patient]);

        return $this->render('consultation/patdash.html.twig', [
            'patient' => $patient,
            'rendezVous' => $rendezVous,
        ]);
    }
}
