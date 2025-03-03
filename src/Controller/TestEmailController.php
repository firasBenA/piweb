<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TestEmailController extends AbstractController
{
    /**
     * @Route("/test-email", name="test_email")
     */
    #[Route("/test-email", name: "test_email")]
    public function testEmail(MailerInterface $mailer)
    {
        $email = (new Email())
            ->from('no-reply@example.com')
            ->to('test@example.com')
            ->subject('Test Email')
            ->text('This is a test email.');

        // Debug statement before sending email
        dump("Test Email Object:", $email);

        $mailer->send($email);

        // Debug statement after sending email
        dump("Test email sent successfully");

        return $this->json(['status' => 'Email sent successfully']);
    }
}
