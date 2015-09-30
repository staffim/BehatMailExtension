<?php

namespace Staffim\Behat\MailExtension\Exception;

use Exception;
use Staffim\Behat\MailExtension\Exception\Exception as BaseException;
use Staffim\Behat\MailExtension\Message;

class MessageException extends BaseException
{
    /**
     * @var Message
     */
    protected $mailMessage;

    /**
     * @var callable
     */
    private $formatter;

    /**
     * @param string $message
     * @param Message $mailMessage
     * @param callable $formatter
     * @param Exception $exception
     */
    public function __construct($message, Message $mailMessage, $formatter = null, Exception $exception = null)
    {
        $this->mailMessage = $mailMessage;
        $this->formatter = $formatter ?: new BaseExceptionFormatter;

        parent::__construct($message, null, $exception);
    }

    /**
     * @return Message
     */
    public function getMailMessage()
    {
        return $this->mailMessage;
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
            $string = (string)$formatter($this->getMessage(), $this->getMailMessage());
        } catch (Exception $e) {
            $string = $this->getMessage();
        }

        return $string;
    }
}
