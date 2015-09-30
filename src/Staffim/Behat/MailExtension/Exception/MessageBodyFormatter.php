<?php

namespace Staffim\Behat\MailExtension\Exception;

use Staffim\Behat\MailExtension\Message;

class MessageBodyFormatter extends BaseExceptionFormatter
{
    /**
     * @param string $text
     * @param Message $message
     *
     * @return string
     */
    public function __invoke($text, Message $message)
    {
        $mailBody = $this->trimString($message->getBody());

        return sprintf("%s\n\nMail message body:\n%s",
            $text,
            $this->pipeString($mailBody."\n")
        );
    }
}
