<?php
namespace App\Command;

// src/Command/SendEmailCommand.php

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(name: 'app:send-email')]
class SendEmailCommand extends Command
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        parent::__construct();
        $this->mailer = $mailer;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (new Email())
            ->from('no-reply@myapp.com')
            ->to('rayenemejri74@gmail.com')
            ->subject('Test Mail')
            ->text('E-mail envoyé via une commande Symfony.')
            ->html('<p>Ceci est un <strong>e-mail de test</strong> envoyé via la console Symfony.</p>');

        $this->mailer->send($email);
        $output->writeln('E-mail envoyé avec succès !');

        return Command::SUCCESS;
    }
}
