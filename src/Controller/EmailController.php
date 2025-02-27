<?php

namespace App\Controller;

use App\Service\MailService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EmailController extends AbstractController
{
    #[Route('/send-email', name: 'send_email')]
    public function sendEmail(MailService $mailService): Response
    {
        $mailService->sendEmail(
            'rayenemejri74@gmail.com',
            'Confirmation de rendez-vous',
            'Votre rendez-vous a été confirmé.',
            '<p>Votre <strong>rendez-vous</strong> a été confirmé avec succès.</p>'
        );

        return new Response('✅ E-mail envoyé avec succès !');
    }
}
