<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Reclamation;
use App\Form\ReclamationType;
use App\Entity\User; // Use User instead of Patient
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ReclamationController extends AbstractController
{
    #[Route('/liste', name: 'reclamation_page')]
public function index(EntityManagerInterface $entityManager, Request $request): Response
{
    // Get the currently logged-in user
    $user = $this->getUser();

    // Get the 'etat' parameter from the request (query string)
    $etat = $request->query->get('etat');

    // Check if the 'etat' is provided and apply the filter accordingly
    if ($etat) {
        // Filter by the selected state and the current user
        $reclamations = $entityManager->getRepository(Reclamation::class)
            ->findBy(['etat' => $etat, 'user' => $user], ['date_debut' => 'DESC']);
    } else {
        // If no filter, fetch all reclamations for the current user
        $reclamations = $entityManager->getRepository(Reclamation::class)
            ->findBy(['user' => $user], ['date_debut' => 'DESC']);
    }

    return $this->render('reclamation/liste.html.twig', [
        'reclamations' => $reclamations,
    ]);
}



#[Route('/ajouter', name: 'ajouter_reclamation')]
public function ajouter(Request $request, EntityManagerInterface $entityManager, Security $security): Response
{
    $reclamation = new Reclamation();
    $form = $this->createForm(ReclamationType::class, $reclamation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Liste des mots interdits
        $forbiddenWords = [
            // Anglais
            'fuck', 'bitch', 'asshole', 'dickhead', 'cunt', 'shit', 'motherfucker',
            // Français
            'pute', 'salope', 'fils de pute', 'enculé', 'ta mère', 'tocard', 'gros con', 'dégage',
           
        ];

        // Récupérer le contenu de la réclamation (supposons un champ 'description')
        $content = $reclamation->getDescription(); // Ajuste selon le nom de ton champ
        $sujet=$reclamation->getSujet();
        // Vérifier si le contenu contient des mots interdits
        $contentLower = strtolower($content);
        foreach ($forbiddenWords as $word) {
            if (strpos($contentLower, $word) !== false) {
                $this->addFlash('error', 'Oops ! Certains mots de votre commentaire ne respectent pas nos règles. Essayez de les reformuler 😊.');
                return $this->render('reclamation/ajouterrec.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
            if (strpos(strtolower($sujet), $word) !== false) {
                $this->addFlash('error', 'Oops ! Certains mots de votre commentaire ne respectent pas nos règles. Essayez de les reformuler 😊.');
                return $this->render('reclamation/ajouterrec.html.twig', [
                    'form' => $form->createView(),
                ]);
            }
        }

        // Get the current logged-in user
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour faire une réclamation.');
        }

        $reclamation->setUser($user);
        $reclamation->setEtat('en_attente');
        $reclamation->setDateDebut(new \DateTime());

        $entityManager->persist($reclamation);
        $entityManager->flush();

        $this->addFlash('success', 'Réclamation ajoutée avec succès !');
        return $this->redirectToRoute('reclamation_page');
    }

    return $this->render('reclamation/ajouterrec.html.twig', [
        'form' => $form->createView(),
    ]);
}

    #[Route('/modifierreclamtion/{id}', name: 'modifier_reclamation')]
    public function modifier(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        // Get the currently logged-in user
        $user = $this->getUser();

        // Check if the reclamation is associated with the current user
        if ($reclamation->getUser() !== $user) {
            // If the reclamation is not created by the logged-in user, deny access
            throw $this->createAccessDeniedException('Vous n\'avez pas la permission de modifier cette réclamation.');
        }

        // Create and handle the form for editing the reclamation
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        // If the form is submitted and valid, update the reclamation in the database
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Flash success message
            $this->addFlash('success', 'Réclamation mise à jour avec succès !');

            // Redirect to the reclamation list page
            return $this->redirectToRoute('reclamation_page');
        }

        // Render the form in the view
        return $this->render('reclamation/modifier.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/supprimer/{id}', name: 'supprimer_reclamation')]
    public function supprimer(Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($reclamation);
        $entityManager->flush();

        return $this->redirectToRoute('reclamation_page');
    }
    #[Route('/statistiques', name: 'reclamation_statistiques')]
    public function statistiques(EntityManagerInterface $entityManager): Response
{
    $repository = $entityManager->getRepository(Reclamation::class);

    // Utilisation de QueryBuilder pour compter les réclamations par état
    $stats = $repository->createQueryBuilder('r')
        ->select('r.etat, COUNT(r.id) as count')
        ->groupBy('r.etat')
        ->getQuery()
        ->getResult();

    // Formater les données pour le graphique
    $labels = [];
    $data = [];

    foreach ($stats as $stat) {
        $labels[] = $stat['etat'];
        $data[] = $stat['count'];
    }

    return $this->render('reclamation/stat.html.twig', [
        'labels' => json_encode($labels),
        'data' => json_encode($data),
    ]);
}

}
