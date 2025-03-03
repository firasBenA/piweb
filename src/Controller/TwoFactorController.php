<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class TwoFactorController extends AbstractController
{
    #[Route('/2fa', name: '2fa_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        $user = $this->getUser();

        return $this->render('security/2fa_login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'userEmail' => $user->getEmail(),
        ]);
    }

    /**
     * @Route("/2fa_check", name="2fa_login_check")
     */
    public function check()
    {
        // This code is never executed.
    }

    /**
     * @Route("/send-2fa-code", name="send_2fa_code")
     */
    public function send2FACode(MailerInterface $mailer)
    {
        $user = $this->getUser();
        $code = rand(100000, 999999); // Generate a random 6-digit code
        $user->setEmailAuthCode($code);

        // Debug statements
        dump("2FA Code:", $code); 
        dump("User Email:", $user->getEmail());

        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($user->getEmail())
            ->subject('Your 2FA Code')
            ->html($this->renderView('security/2fa_email.html.twig', ['code' => $code]));

        // Debug statement before sending email
        dump("Email Object:", $email);

        $mailer->send($email);

        // Debug statement after sending email
        dump("Email sent successfully");

        return $this->redirectToRoute('2fa_login', [
            'userEmail' => $user->getEmail(),
        ]);
    }
}
