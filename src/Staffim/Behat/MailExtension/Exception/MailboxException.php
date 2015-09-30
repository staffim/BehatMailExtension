<?php

namespace Staffim\Behat\MailExtension\Exception;

use Exception;
use Staffim\Behat\MailExtension\Exception\Exception as BaseException;
use Staffim\Behat\MailExtension\Mailbox;

class MailboxException extends BaseException
{
    /**
     * @var Mailbox
     */
    protected $mailbox;

    /**
     * @param string $message
     * @param Mailbox $mailbox
     * @param Exception $exception
     */
    public function __construct($message, Mailbox $mailbox, Exception $exception = null)
    {
        $this->mailbox = $mailbox;

        parent::__construct($message, null, $exception);
    }

    /**
     * @return Mailbox
     */
    public function getMailbox()
    {
        return $this->mailbox;
    }

    /**
     * Returns exception message with additional context info.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $string = sprintf("%s\n\nMail messages:\n%s",
                $this->getMessage(),
                $this->pipeString((string)$this->getMailbox())
            );
        } catch (Exception $e) {
            return $this->getMessage();
        }

        return $string;
    }

    /**
     * Prepends every line in a string with pipe (|).
     *
     * @param string $string
     *
     * @return string
     */
    // TODO Use method from BaseExceptionFormatter.
    private function pipeString($string)
    {
        return '|  ' . strtr($string, array("\n" => "\n|  "));
    }
}
