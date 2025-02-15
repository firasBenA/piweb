<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Form\ReponseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/reponse')]
final class ResponseController extends AbstractController{
    #[Route('/liste', name: 'reponse_reclamation_page')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $reclamations = $entityManager->getRepository(Reclamation::class)->findAll();

        return $this->render('reponse/liste.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }
    #[Route('/ajouter/{id}', name: 'ajouter_reponse')]
    public function ajouter(Reclamation $reclamation, Request $request, EntityManagerInterface $entityManager): Response
    {
        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reponse->setReclamation($reclamation);
            $reponse->setDateDeReponse(new \DateTime()); // Définir la date actuelle

            $reclamation->setEtat('traité');
            $entityManager->persist($reponse);
            $entityManager->persist($reclamation); // Mettre à jour la réclamation

            $entityManager->flush();

            $this->addFlash('success', 'Réponse ajoutée avec succès et réclamation marquée comme traitée !');
            return $this->redirectToRoute('reclamation_page');
        }

        return $this->render('reponse/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
