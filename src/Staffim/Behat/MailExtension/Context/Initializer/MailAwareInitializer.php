<?php

namespace Staffim\Behat\MailExtension\Context\Initializer;

use Behat\Behat\Context\ContextInterface;
use Behat\Behat\Context\Initializer\InitializerInterface;

use Staffim\Behat\MailExtension\Account;
use Staffim\Behat\MailExtension\Context\MailAwareInterface;
use Staffim\Behat\MailExtension\MailAgent;

class MailAwareInitializer implements InitializerInterface
{
    private $mailAgent;
    private $parameters;

    public function __construct(array $parameters)
    {
        $pop3Account = new Account($parameters['pop3Server'], $parameters['pop3Auth']);
        $smtpAccount = new Account($parameters['smtpServer'], $parameters['smtpAuth']);
        $this->mailAgent = new MailAgent($pop3Account, $smtpAccount);
        $this->parameters = $parameters;
    }

    public function supports(ContextInterface $context)
    {
        return ($context instanceof MailAwareInterface);
    }

    public function initialize(ContextInterface $context)
    {
        $context->setMailAgent($this->mailAgent);
        $context->setMailAgentParameters($this->parameters);
    }
}
