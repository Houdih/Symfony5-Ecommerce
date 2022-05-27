<?php

namespace App\Service;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;

class MailerService
{
    private $mailer;

    public function __construct(MailerInterface $mailer) {
        $this->mailer = $mailer;
    }    

    public function sendEmail($to = 'tests.houd@gmail.com', $subject = 'Sujet du mail', $content = "Le contenu du mail"): void
    {
        $email = (new Email())
            ->from('tests.houd@example.com')
            ->to($to)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($subject)
            ->text('Sending emails is fun again!')
            ->html($content);

            $this->mailer->send($email);

    }
}
