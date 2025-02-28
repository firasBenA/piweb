<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\EmailService;

use App\Entity\Evenement;

use Symfony\Component\Security\Http\Attribute\IsGranted;

class EmailController extends AbstractController
{
    private $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    #[Route('/evenement/send-email/{id}', name: 'app_evenement_send_email', methods: ['POST'])]
    #[IsGranted('ROLE_PATIENT')]
    public function sendEmail(Evenement $evenement): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException("Vous devez être connecté pour participer.");
        }

        $subject = "Confirmation de participation - " . $evenement->getNom();
        $message = "Vous avez confirmé votre participation à l'événement: " . $evenement->getNom() . ".\n\n".
                   "Lieu: " . $evenement->getLieuxEvent() . "\n".
                   "Date: " . $evenement->getDateEvent()->format('d M Y') . "\n\n".
                   "Merci pour votre participation !";

        $this->emailService->sendEmail($user->getUserIdentifier(), $subject, $message);

        $this->addFlash('success', 'Votre participation a été confirmée, un email de confirmation a été envoyé.');

        return $this->redirectToRoute('app_evenement_index');
    }
}
