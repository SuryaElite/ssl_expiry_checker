<?php

namespace Ssl\Helper;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class MailerController
{

    public function sendEmail($subject, $text)
    {

        $email = (new Email())
            ->from(new Address($_ENV["MAIL_FROM_ADDRESS"], $_ENV["MAIL_FROM_NAME"]))
            ->to($_ENV["MAIL_TO_ADDRESS"])
            ->replyTo($_ENV["MAIL_REPLY_TO_ADDRESS"])
            ->subject($subject)
//            ->text('Sending emails is fun again!')
            ->html($text);

        $transport = Transport::fromDsn($_ENV["MAILER_DSN"]);
        $mailer = new Mailer($transport);
        $mailer->send($email);
    }
}
