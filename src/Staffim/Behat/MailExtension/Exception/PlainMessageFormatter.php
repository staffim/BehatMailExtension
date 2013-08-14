<?php

namespace Staffim\Behat\MailExtension\Exception;

use Staffim\Behat\MailExtension\Message;

class PlainMessageFormatter extends BaseExceptionFormatter
{

    /**
     * @param string $message
     * @param \Staffim\Behat\MailExtension\Message $mail
     *
     * @return string
     */
    public function __invoke($message, Message $mail)
    {
        $mailBody = $this->trimString($mail->getPlainMessage());

        return sprintf("%s\n\nPlain message:\n%s",
            $message,
            $this->pipeString($mailBody."\n")
        );
    }
}
