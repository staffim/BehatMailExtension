<?php

namespace Staffim\Behat\MailExtension\Exception;

use Staffim\Behat\MailExtension\Message;

class MessageBodyFormatter extends BaseExceptionFormatter
{

    /**
     * @param string $message
     * @param \Staffim\Behat\MailExtension\Message $mail
     *
     * @return string
     */
    public function __invoke($message, Message $mail)
    {
        $mailBody = $this->trimString($mail->getBody());

        return sprintf("%s\n\nMail body:\n%s",
            $message,
            $this->pipeString($mailBody."\n")
        );
    }
}
