<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Form\PanierType;
use App\Repository\PanierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/panier')]
final class PanierController extends AbstractController
{
    #[Route(name: 'app_panier_index', methods: ['GET'])]
    public function index(PanierRepository $panierRepository): Response
    {
        return $this->render('panier/index.html.twig', [
            'paniers' => $panierRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_panier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $panier = new Panier();
        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($panier);
                $entityManager->flush();

                $this->addFlash('success', 'Le panier a été créé avec succès.');
                return $this->redirectToRoute('app_panier_index');
            } else {
                $this->addFlash('error', 'Erreur lors de la création du panier. Veuillez vérifier les champs.');
            }
        }

        return $this->render('panier/new.html.twig', [
            'panier' => $panier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_panier_show', methods: ['GET'])]
    public function show(Panier $panier): Response
    {
        return $this->render('panier/show.html.twig', [
            'panier' => $panier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_panier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Panier $panier, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $panier->setMajLe(new \DateTime()); // Automatically update modification timestamp
                $entityManager->flush();

                $this->addFlash('success', 'Le panier a été mis à jour avec succès.');
                return $this->redirectToRoute('app_panier_index');
            } else {
                $this->addFlash('error', 'Erreur lors de la mise à jour du panier. Vérifiez vos champs.');
            }
        }

        return $this->render('panier/edit.html.twig', [
            'panier' => $panier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_panier_delete', methods: ['POST'])]
    public function delete(Request $request, Panier $panier, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$panier->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($panier);
                $entityManager->flush();

                $this->addFlash('success', 'Le panier a été supprimé avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la suppression du panier.');
            }
        } else {
            $this->addFlash('error', 'Échec de la suppression du panier. Jeton CSRF invalide.');
        }

        return $this->redirectToRoute('app_panier_index');
    }
}
