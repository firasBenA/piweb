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

        // Define the image path (store the event image in public/images/)
        $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/images/event_banner.png';

        // HTML message with styling
        $message = "
        <div style='max-width: 600px; margin: 20px auto; padding: 20px; 
                    border-radius: 10px; box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); 
                    font-family: Arial, sans-serif; background-color: #ffffff;'>
            <h2 style='color: #333; text-align: center;'>Confirmation de participation</h2>
            <p style='text-align: center;'>
                <img src='cid:event_banner' alt='Event Banner' style='max-width: 100%; border-radius: 10px;' />
            </p>
            <p style='font-size: 16px; color: #555;'>
                Vous avez confirmé votre participation à l'événement: 
                <strong>{$evenement->getNom()}</strong>.
            </p>
            <p style='font-size: 16px; color: #555;'><strong>📍 Lieu:</strong> {$evenement->getLieuxEvent()}</p>
            <p style='font-size: 16px; color: #555;'><strong>📅 Date:</strong> {$evenement->getDateEvent()->format('d M Y')}</p>
            <p style='font-size: 16px; color: #555;'>Merci pour votre participation !</p>
        </div>";

        // Send the email with embedded image
        $this->emailService->sendEmail($user->getUserIdentifier(), $subject, $message, $imagePath);

        $this->addFlash('success', 'Votre participation a été confirmée, un email de confirmation a été envoyé.');

        return $this->redirectToRoute('app_evenement_index');
    }


    
}
