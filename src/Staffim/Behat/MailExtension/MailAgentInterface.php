<?php

namespace Staffim\Behat\MailExtension;

use Mailbox;

interface MailAgentInterface
{
     /**
     * @return Mailbox
     */
    public function getMailbox();

    /**
     * Receive messages to mailbox
     *
     * @return Mailbox
     */
    public function receive();

    /**
     * @param \ezcMail $mail
     */
    public function send($mail);

    /**
     * Remove messages from server
     */
    public function removeMessages();

    /**
     * Waits some time or number messages.
     *
     * @param integer $time      time in milliseconds
     * @param integer  $number
     *
     * @return bool
     */
    public function wait($time, $number);
}
