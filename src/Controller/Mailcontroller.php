<?php

namespace App\Controller;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class Mailcontroller
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmail()
    {
        $email = (new Email())
            ->from('info@stuvent.nl')
            ->to('koen.green1@gmail.com')
            ->subject('Test email')
            ->text('Je inschrjving is verwijderd.');

        $this->mailer->send($email);
    }

}