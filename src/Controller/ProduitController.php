<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/produit')]
final class ProduitController extends AbstractController
{

    #[Route(name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }
    #[Route('/admin',name: 'app_produit_admin_index', methods: ['GET'])]
    public function indexAdmin(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/indexAdmin.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }
    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        // Set initial timestamps before form handling
        $produit->setCreerLe(new \DateTime());
        $produit->setMajLe(new \DateTime());

        // Do NOT flush yet; wait until form is submitted & valid
        // $entityManager->flush(); <-- Removed this line

        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );
                $produit->setImage($newFilename);
            }
            
            // Persist the newly created product
            $entityManager->persist($produit);
            // Finally flush after form is submitted & valid
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }
    #[Route('/apply-discount', name: 'apply_discount', methods: ['POST'])]
    public function applyDiscount(ProduitRepository $produitRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        // Retrieve products sorted by lowest sales
        $produits = $produitRepository->findBy([], ['ventes' => 'ASC'], 5); // Get 5 least bought

        foreach ($produits as $produit) {
            $newPrice = $produit->getPrix() * 0.8; // Apply 20% discount
            $produit->setPrix($newPrice);
        }

        $entityManager->flush();

        return new JsonResponse(['status' => 'success'], 200);
    }
    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move(
                    $this->getParameter('uploads_directory'),
                    $newFilename
                );
                $produit->setImage($newFilename);
            }

            // Manually update MajLe to current date/time on every edit
            $produit->setMajLe(new \DateTime());

            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        // Check CSRF token. Use request->request->get('_token') (the usual approach in Symfony)
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->request->get('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/cart/products', name: 'app_cart', methods: ['GET'])]
    public function cart(): Response
    {
        return $this->render('/panier/cart.html.twig');
    }
    #[Route('/produits/statistiques', name: 'app_produit_statistiques', methods: ['GET'])]
    public function statistiques(ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findAll();

        $produitLabels = [];
        $ventesData = [];

        foreach ($produits as $produit) {
            $produitLabels[] = $produit->getNom();
            $ventesData[] = $produit->getVentes();
        }

        return $this->render('produit/statistiques.html.twig', [
            'produitLabels' => json_encode($produitLabels),
            'ventesData' => json_encode($ventesData),
        ]);
    }

}
