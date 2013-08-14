<?php

namespace Staffim\Behat\MailExtension\Exception;

use Behat\Mink\Exception\Exception;
use Staffim\Behat\MailExtension\Mailbox;

class MailboxException extends Exception
{
    /**
     * Mailbox instance.
     *
     * @var \Staffim\Behat\MailExtension\Mailbox
     */
    protected $mailbox;

    /**
     * @return \Staffim\Behat\MailExtension\Mailbox
     */
    public function getMailbox()
    {
        return $this->mailbox;
    }

    /**
     * Initializes exception.
     *
     * @param string $message   optional
     * @param \Staffim\Behat\MailExtension\Mailbox $mailbox
     * @param \Behat\Mink\Exception\Exception $exception
     */
    public function __construct($message = null, Mailbox $mailbox, Exception $exception = null)
    {
        $this->mailbox = $mailbox;

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
            $mailboxList = $this->trimString($this->getMailbox()->getMailFromToSubject());
            $string = sprintf("%s\n\nMail messages:\n%s",
                $this->getMessage(),
                $this->pipeString($mailboxList."\n")
            );
        } catch (\Exception $e) {
            return $this->getMessage();
        }

        return $string;
    }
}
