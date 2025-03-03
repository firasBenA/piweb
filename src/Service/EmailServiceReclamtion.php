<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\RelatedPart;
use Symfony\Component\Mime\Part\File;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;

class EmailServiceReclamtion
{
    private $mailer;

    public function __construct()
    {
        $transport = Transport::fromDsn($_ENV['MAILER_DSN']);
        $this->mailer = new Mailer($transport);
    }

    public function sendEmail(string $to, string $subject, string $content)//
    {
        $email = (new Email())
            ->from('esprit.recover.plus@gmail.com')
            ->to($to)//
            ->subject($subject)
            ->html($content); // Send HTML formatted email

      

        $this->mailer->send($email);
    }
}