<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\TwilioService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SmsController extends AbstractController
{
    private TwilioService $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }


    #[Route('/send-sms', name: 'send_sms', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function sendSms(): JsonResponse
    {
        $user = $this->getUser();
    
        // Force Symfony to recognize it as an instance of User
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not found or incorrect instance'], 403);
        }
    
        // Now you can access methods like getTelephone()
        $phoneNumber = '+216' . (string) $user->getTelephone();
    
        if (!$phoneNumber) {
            return new JsonResponse(['error' => 'No phone number found for this user'], 400);
        }
    
        $message = 'Allez voir votre ordonnance!';
    
        try {
            $this->twilioService->sendSms($phoneNumber, $message);
            return new JsonResponse(['status' => 'SMS sent successfully to ' . $phoneNumber]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
    
}
