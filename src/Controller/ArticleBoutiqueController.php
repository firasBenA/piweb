<?php

namespace App\Controller;

use App\Entity\ArticleBoutique;
use App\Form\ArticleBoutiqueType;
use App\Repository\ArticleBoutiqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/article/boutique')]
final class ArticleBoutiqueController extends AbstractController
{
    #[Route(name: 'app_article_boutique_index', methods: ['GET'])]
    public function index(ArticleBoutiqueRepository $articleBoutiqueRepository): Response
    {
        return $this->render('article_boutique/index.html.twig', [
            'article_boutiques' => $articleBoutiqueRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_article_boutique_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $articleBoutique = new ArticleBoutique();
        $form = $this->createForm(ArticleBoutiqueType::class, $articleBoutique);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                // Process the image file
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    // Move the file to the directory where images are stored
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'An error occurred while uploading the image.');
                    return $this->redirectToRoute('app_article_boutique_new');
                }

                // Set the image filename in the ArticleBoutique entity
                $articleBoutique->setImage($newFilename);
            } else {
                $articleBoutique->setImage(null); // No image uploaded, set to null
            }

            $entityManager->persist($articleBoutique);
            $entityManager->flush();

            return $this->redirectToRoute('app_article_boutique_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article_boutique/new.html.twig', [
            'article_boutique' => $articleBoutique,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_article_boutique_show', methods: ['GET'])]
    public function show(ArticleBoutique $articleBoutique): Response
    {
        return $this->render('article_boutique/show.html.twig', [
            'articleBoutique' => $articleBoutique,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_article_boutique_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ArticleBoutique $articleBoutique, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ArticleBoutiqueType::class, $articleBoutique);
        $form->handleRequest($request);

        $currentImage = $articleBoutique->getImage(); // Keep track of the current image

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                // Process the new image
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    // Move the new image to the correct directory
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'An error occurred while uploading the new image.');
                    return $this->redirectToRoute('app_article_boutique_edit', ['id' => $articleBoutique->getId()]);
                }

                // Set the new image filename
                $articleBoutique->setImage($newFilename);
            } else {
                // Keep the old image if no new one is uploaded
                $articleBoutique->setImage($currentImage);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_article_boutique_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('article_boutique/edit.html.twig', [
            'articleBoutique' => $articleBoutique,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_article_boutique_delete', methods: ['POST'])]
    public function delete(Request $request, ArticleBoutique $articleBoutique, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $articleBoutique->getId(), $request->request->get('_token'))) {
            $imageFilePath = $this->getParameter('images_directory') . '/' . $articleBoutique->getImage();

            if ($imageFilePath && file_exists($imageFilePath)) {
                unlink($imageFilePath); // Delete the image file from the server
            }

            $entityManager->remove($articleBoutique);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_article_boutique_index', [], Response::HTTP_SEE_OTHER);
    }
}
