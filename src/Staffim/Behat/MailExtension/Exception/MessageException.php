<?php

namespace Staffim\Behat\MailExtension\Exception;

use Behat\Mink\Exception\Exception;
use Staffim\Behat\MailExtension\Message;

class MessageException extends Exception
{
    /**
     * Mail message instance.
     *
     * @var \Staffim\Behat\MailExtension\Message
     */
    protected $mailMessage;

    /**
     * @return \Staffim\Behat\MailExtension\Message
     */
    public function getMailMessage()
    {
        return $this->mailMessage;
    }

    /**
     * Initializes exception.
     *
     * @param string $message   optional message
     * @param \Staffim\Behat\MailExtension\Message $mailMessage
     * @param Exception $exception
     */
    public function __construct($message = null, Message $mailMessage, Exception $exception = null)
    {
        $this->mailMessage = $mailMessage;

        parent::__construct($message ?: $exception->getMessage(), null,  $exception);
    }

    /**
     * Returns exception message with additional context info.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $mailBody = $this->trimString($this->getMailMessage()->getRawParsedMessage());
            $string = sprintf("%s\n\nRaw message:\n%s",
                $this->getMessage(),
                $this->pipeString($mailBody."\n")
            );
        } catch (\Exception $e) {
            return $this->getMessage();
        }

        return $string;
    }
}
