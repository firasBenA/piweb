<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Entity\Consultation;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;
use App\Form\ModifConType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Service\MailService;

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
    public function listForMedecin(Security $security, EntityManagerInterface $entityManager): Response
    {
        // Get the logged-in medecin (assumed that user is a Medecin)
        $medecin = $security->getUser();

        if (!$medecin) {
            throw $this->createAccessDeniedException('You are not logged in or not a medecin.');
        }

        // Fetch the rendezvous for the logged-in medecin
        $rendezVous = $entityManager->getRepository(RendezVous::class)->findBy(['medecin' => $medecin]);

        return $this->render('consultation/listcon.html.twig', [
            'medecin' => $medecin,
            'rendezVous' => $rendezVous,
        ]);
    }


    #[Route('/approuver/{id}', name: 'rendezvous_approuver', methods: ['POST'])]
public function approuverRendezVous(Request $request, RendezVous $rendezVous, EntityManagerInterface $entityManager, MailService $mailService): Response
{
    $user = $this->getUser();

    if (!in_array('MEDECIN', $user->getRoles()) || $rendezVous->getMedecin() !== $user) {
        throw new AccessDeniedException('Accès interdit : vous ne pouvez approuver que vos propres rendez-vous.');
    }

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
        if (!in_array('MEDECIN', $user->getRoles()) || $rendezVous->getMedecin() !== $user) {
            throw new AccessDeniedException('Accès interdit : vous ne pouvez refuser que vos propres rendez-vous.');
        }

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

        // Vérifier si l'utilisateur a le rôle ROLE_MEDECIN
        if (!in_array('MEDECIN', $user->getRoles()) || $rendezVous->getMedecin() !== $user) {
            throw new AccessDeniedException('Accès interdit : vous ne pouvez modifier que vos propres rendez-vous.');
        }

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
                    'Votre rendez-vous a été approuvé',
                    'Votre rendez-vous avec le médecin a été approuvé.',
                    '<p>Bonjour ' . $patient->getNom() . ',</p>
                    <p>Votre rendez-vous avec le médecin ' . $rendezVous->getMedecin()->getNom() . ' a été approuvé.</p>
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

    #[Route('/medash', name: 'medecin_dashboard1')]
    public function dashboard(Security $security, EntityManagerInterface $entityManager): Response
    {
        // Get the logged-in medecin (assumed that user is a Medecin)
        $medecin = $security->getUser();
    
        if (!$medecin instanceof User || !in_array('MEDECIN', $medecin->getRoles())) {
            throw $this->createAccessDeniedException('Accès refusé : Seuls les patients peuvent accéder à ce tableau de bord.');
        }
    
        // Récupérer les consultations du médecin connecté
        $consultations = $entityManager->getRepository(Consultation::class)->findBy(['medecin' => $medecin]);
    
        return $this->render('consultation/meddash.html.twig', [
            'medecin' => $medecin,
            'consultations' => $consultations, // Correction ici
        ]);
    }
    

    #[Route('/infomed', name: 'infomed')]
public function show(Security $security): Response
{
    // Récupérer le médecin connecté
    $medecin = $security->getUser();

    if (!$medecin) {
        throw $this->createAccessDeniedException('Vous n\'êtes pas connecté ou vous n\'êtes pas un médecin.');
    }

    return $this->render('consultation/infomed.html.twig', [
        'medecin' => $medecin,
    ]);
}




}