<?php

namespace App\Security;

use Scheb\TwoFactorBundle\Mailer\AuthCodeMailerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class TwoFactorMailer implements AuthCodeMailerInterface
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendAuthCode(\Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface $user): void
    {
        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($user->getEmail())
            ->subject('Your 2FA Code')
            ->html($this->twig->render('security/2fa_email.html.twig', ['code' => $user->getEmailAuthCode()]));

        $this->mailer->send($email);
    }
}
