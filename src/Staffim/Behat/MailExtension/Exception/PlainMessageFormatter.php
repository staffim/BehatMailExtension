<?php

namespace Staffim\Behat\MailExtension\Exception;

use Staffim\Behat\MailExtension\Message;

class PlainMessageFormatter extends BaseExceptionFormatter
{

    /**
     * @param string $text
     * @param \Staffim\Behat\MailExtension\Message $message
     *
     * @return string
     */
    public function __invoke($text, Message $message)
    {
        $mailBody = $this->trimString($message->getPlainMessage());

        return sprintf("%s\n\nPlain message:\n%s",
            $text,
            $this->pipeString($mailBody."\n")
        );
    }
}
