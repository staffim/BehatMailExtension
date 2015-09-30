<?php

// Placing this functions inside Staffim\Behat\MailExtension or Staffim\Behat\MailExtension\Context namespace isn't
// working in PHPStorm (code completion).
namespace Staffim\Behat\MailExtension\X;

use Staffim\Behat\MailExtension\Mailbox;
use Staffim\Behat\MailExtension\Message;

use function Colada\x;

if (!function_exists('\\Staffim\\Behat\\MailExtension\\X\\mailbox')) {
    /**
     * Alias (wrapper) to define return value type and use code completion.
     *
     * @return Mailbox
     */
    function mailbox()
    {
        return x();
    }
}

if (!function_exists('\\Staffim\\Behat\\MailExtension\\X\\message')) {
    /**
     * Alias (wrapper) to define return value type and use code completion.
     *
     * @return Message
     */
    function message()
    {
        return x();
    }
}
