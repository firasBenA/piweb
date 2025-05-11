<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Entity\Patient;
use App\Entity\Medecin;
use App\Entity\User;
use App\Form\RendezVousType;
use App\Form\ModifConType;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ConsultationController extends AbstractController
{
    #[Route('/consultation', name: 'app_consultation')]
    public function index(): Response
    {
        return $this->render('consultation/index.html.twig', [
            'controller_name' => 'ConsultationController',
        ]);
    }

   #[Route('/listcon', name: 'consultation_medecin_list')]
    public function listForMedecin(
        Security $security,
        EntityManagerInterface $entityManager,
        Request $request
    ): Response {
        // Récupérer le médecin connecté
        $medecin = $security->getUser();
    
        if (!$medecin) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas connecté ou n\'êtes pas un médecin.');
        }
    
        // Récupérer les paramètres de pagination et de filtre
        $page = $request->query->getInt('page', 1); // Page actuelle
        $limit = 3; // Nombre d'éléments par page
        $typeRdv = $request->query->get('typeRdv'); // Filtre par type de rendez-vous
    
        // Construire la requête en fonction du filtre
        $repository = $entityManager->getRepository(RendezVous::class);
        $queryBuilder = $repository->createQueryBuilder('r')
            ->where('r.medecin = :medecin')
            ->setParameter('medecin', $medecin);
    
        // Appliquer le filtre par type de rendez-vous si un type est sélectionné
        if ($typeRdv) {
            $queryBuilder->andWhere('r.type_rdv = :typeRdv')
                ->setParameter('typeRdv', $typeRdv);
        }
    
        // Pagination
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($queryBuilder);
        $totalItems = count($paginator);
        $pagesCount = ceil($totalItems / $limit);
    
        $rendezVous = $paginator
            ->getQuery()
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getResult();
    
        // Retourner une réponse JSON si c'est une requête AJAX
        if ($request->isXmlHttpRequest()) {
            $rendezVousArray = [];
            foreach ($rendezVous as $rdv) {
                $rendezVousArray[] = [
                    'id' => $rdv->getId(),
                    'date' => $rdv->getDate()->format('d/m/Y'),
                    'typeRdv' => $rdv->getTypeRdv(),
                    'cause' => $rdv->getCause(),
                    'patient' => $rdv->getPatient()->getNom() . ' ' . $rdv->getPatient()->getPrenom(),
                    'statut' => $rdv->getStatut(),
                ];
            }
    
            return $this->json([
                'rendezVous' => $rendezVousArray,
                'page' => $page,
                'pagesCount' => $pagesCount,
            ]);
        }
    
        // Retourner la vue Twig normale
        return $this->render('consultation/listcon.html.twig', [
            'medecin' => $medecin,
            'rendezVous' => $rendezVous,
            'page' => $page,
            'pagesCount' => $pagesCount,
            'typeRdv' => $typeRdv,
        ]);
    }


    #[Route('/approuver/{id}', name: 'rendezvous_approuver', methods: ['POST'])]
public function approuverRendezVous(Request $request, RendezVous $rendezVous, EntityManagerInterface $entityManager, MailService $mailService): Response
{
    $user = $this->getUser();

    

    // Récupération des données du formulaire
    $heure = $request->request->get('heure');
    $prix = $request->request->get('prix');

    if (!$heure || !$prix) {
        $this->addFlash('danger', 'Veuillez fournir une heure et un prix.');
        return $this->redirectToRoute('consultation_medecin_list');
    }

    // Mise à jour du statut du rendez-vous
    $rendezVous->setStatut('Approuvé');
    $entityManager->flush();

    // Envoi de l'email au patient
    $patient = $rendezVous->getPatient();
    $dateRdv = $rendezVous->getDate()->format('d/m/Y');
    $mailService->sendEmail(
        $patient->getEmail(),
        'Votre rendez-vous a été approuvé',
        'Votre rendez-vous avec le médecin a été approuvé.',
        "<p>Bonjour {$patient->getNom()},</p>
        <p>Votre rendez-vous avec le Dr. {$rendezVous->getMedecin()->getNom()} a été approuvé.</p>
        <p>📅 Date: <strong>{$dateRdv}</strong></p>
        <p>⏰ Heure: <strong>{$heure}</strong></p>
        <p>💰 Prix: <strong>{$prix} DT</strong></p>
        <p>Merci de votre confiance.</p>"
    );

    $this->addFlash('success', 'Le rendez-vous a été approuvé et un email a été envoyé au patient.');

    return $this->redirectToRoute('consultation_medecin_list');
}


    #[Route('/refuser/{id}', name: 'rendezvous_refuser')]
    public function refuserRendezVous(RendezVous $rendezVous, EntityManagerInterface $entityManager, MailService $mailService): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Vérifier si l'utilisateur a le rôle ROLE_MEDECIN
       

        // Refuser le rendez-vous
        $rendezVous->setStatut('Refusé');
        $entityManager->flush();

        $patient = $rendezVous->getPatient();
        $mailService->sendEmail(
            $patient->getEmail(),
            'Votre rendez-vous a été refusé',
            'Votre rendez-vous avec le médecin a été refusé.',
            '<p>Bonjour ' . $patient->getNom() . ',</p>
            <p>Votre rendez-vous avec le médecin ' . $rendezVous->getMedecin()->getNom() . ' a été refusé.</p>
            <p>Merci de choisir un autre medecin ou une autre date.</p>'
        );

        $this->addFlash('danger', 'Le rendez-vous a été refusé.');

        return $this->redirectToRoute('consultation_medecin_list');
    }

    #[Route('/modifier/{id}', name: 'rendezvous_modifier')]
    public function modifierRendezVous(Request $request, RendezVous $rendezVous, EntityManagerInterface $entityManager, MailService $mailService): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

       

        // Créer le formulaire de modification de rendez-vous
        $form = $this->createForm(ModifConType::class, $rendezVous);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Vérifier si la date est vide après soumission
            if ($rendezVous->getDate() === null) {
                // Ajouter un message d'erreur si la date est vide
                $this->addFlash('error', 'Veuillez remplir la date.');
                // Afficher à nouveau le formulaire avec les erreurs
                return $this->render('consultation/modifier.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Si le formulaire est valide, mettre à jour le statut
            if ($form->isValid()) {
                $rendezVous->setStatut('Approuvé');
                $entityManager->flush();

                $patient = $rendezVous->getPatient();
                $mailService->sendEmail(
                    $patient->getEmail(),
                    'Votre rendez-vous a été modifié',
                    'Votre rendez-vous avec le médecin a été modifié.',
                    '<p>Bonjour ' . $patient->getNom() . ',</p>
                    <p>Votre rendez-vous avec le médecin ' . $rendezVous->getMedecin()->getNom() . ' a été modifié.</p>
                    <p>Nous vous remercions pour votre patience et nous vous attendons à la consultation.</p>'
                );

                // Message de succès
                $this->addFlash('success', 'Le rendez-vous a été modifié et approuvé.');

                // Rediriger vers la liste des consultations
                return $this->redirectToRoute('consultation_medecin_list');
            } else {
                // En cas d'erreur de validation
                $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
            }
        }

        return $this->render('consultation/modifier.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('medash', name: 'medecinDashboard')]
    public function dashboard(Security $security): Response
    {
        // Get the logged-in user
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('You are not logged in.');
        }

        // Render the dashboard page with the logged-in user
        return $this->render('consultation/meddash.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/infomed', name: 'infomed')]
    public function show(Security $security, EntityManagerInterface $entityManager): Response
    {
        // Get the logged-in medecin (assumed that user is a Medecin)
        $medecin = $security->getUser();

        if (!$medecin) {
            throw $this->createAccessDeniedException('You are not logged in or not a medecin.');
        }

        // Render the template with the logged-in medecin
        return $this->render('consultation/infomed.html.twig', [
            'medecin' => $medecin,
        ]);
    }
}
