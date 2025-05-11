<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail(string $to, string $subject, string $textContent, string $htmlContent): void
    {
        $email = (new Email())
            ->from('no-reply@ehealth.com') 
            ->to($to)
            ->subject($subject)
            ->text($textContent)
            ->html($htmlContent);

        $this->mailer->send($email);
    }
}