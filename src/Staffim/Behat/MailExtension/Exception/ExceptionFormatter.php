<?php

namespace Staffim\Behat\MailExtension\Exception;

use Staffim\Behat\MailExtension\Message;

interface ExceptionFormatter {
    /**
     * @param String $message
     * @param \Staffim\Behat\MailExtension\Message $mail
     * @return mixed
     */
    function __invoke($message, Message $mail);
}
