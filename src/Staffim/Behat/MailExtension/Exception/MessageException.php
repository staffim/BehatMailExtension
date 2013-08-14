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
     * @var ExceptionFormatter
     */
    private $formatter;

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
     * @param ExceptionFormatter $formatter
     * @param Exception $exception
     */
    public function __construct($message = null, Message $mailMessage, ExceptionFormatter $formatter = null, Exception $exception = null)
    {
        $this->mailMessage = $mailMessage;
        $this->formatter = $formatter ?: new BaseExceptionFormatter;

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
            $formatter = $this->formatter;
            $string = $formatter($this->getMessage(), $this->getMailMessage());
        } catch (\Exception $e) {
            return $this->getMessage();
        }

        return $string;
    }
}
