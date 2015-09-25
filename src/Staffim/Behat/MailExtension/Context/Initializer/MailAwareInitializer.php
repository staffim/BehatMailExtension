<?php

namespace Staffim\Behat\MailExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use Staffim\Behat\MailExtension\Account;
use Staffim\Behat\MailExtension\Context\MailAwareInterface;
use Staffim\Behat\MailExtension\MailAgent;

class MailAwareInitializer implements ContextInitializer
{
    private $mailAgent;
    private $parameters;

    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;

        $pop3Account = new Account(
            $parameters['pop3_host'],
            $parameters['pop3_port'],
            $parameters['pop3_user'],
            $parameters['pop3_password'],
            $parameters['pop3_secure']
        );
        $smtpAccount = new Account(
            $parameters['smtp_host'],
            $parameters['smtp_port'],
            $parameters['smtp_user'],
            $parameters['smtp_password'],
            $parameters['smtp_secure']
        );

        $this->mailAgent = new MailAgent($pop3Account, $smtpAccount);
    }

    /**
     * Initializes provided context.
     *
     * @param Context $context
     */
    public function initializeContext(Context $context)
    {
        if (!$context instanceof MailAwareInterface) {
            return;
        }

        $context->setMailAgent($this->mailAgent);
        $context->setMailAgentParameters($this->parameters);
    }
}
