<?php

namespace App\Controller;

use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LikeController extends AbstractController
{
    #[Route('/article/{id}/like', name: 'article_like', methods: ['POST'])]
    #[IsGranted('ROLE_PATIENT')]
    public function like(Article $article, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 403);
        }

        if ($article->isLikedByUser($user)) {
            $article->unlike($user);
            $message = 'Like removed';
        } else {
            $article->like($user);
            $message = 'Article liked';
        }

        $entityManager->persist($article);
        $entityManager->flush();

        return new JsonResponse([
            'message' => $message,
            'likes' => count($article->getLikedByUsers())
        ]);
    }
}