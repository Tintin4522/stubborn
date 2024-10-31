<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendConfirmationEmail($user, $signedUrl)
    {
        $email = (new Email())
            ->from('no-reply@example.com')
            ->to($user->getEmail())
            ->subject('Confirmation d\'Email')
            ->html(
                '<p>Bonjour ' . $user->getUsername() . ',</p>' .
                '<p>Veuillez confirmer votre adresse email en cliquant sur le lien ci-dessous :</p>' .
                '<a href="' . $signedUrl . '">Confirmer mon email</a>' .
                '<p>Ce lien expirera dans 24 heures.</p>'
            );

        $this->mailer->send($email);
    }
}
