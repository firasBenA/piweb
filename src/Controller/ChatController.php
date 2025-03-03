<?php

namespace App\Controller;

use App\Service\GeminiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class ChatController extends AbstractController
{
    #[Route('/chat', name: 'app_chat')]
    public function chat(Request $request, GeminiService $geminiService)
    {
        if ($request->isXmlHttpRequest()) {
            $message = $request->request->get('message');
            $response = $geminiService->sendMessage($message);

            return new JsonResponse($response); // Renvoyer directement la JsonResponse
        }

        return $this->render('chat/index.html.twig');
    }
}
