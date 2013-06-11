<?php

namespace Staffim\Behat\MailExtension\Context\Initializer;

use Behat\Behat\Context\ContextInterface;
use Behat\Behat\Context\Initializer\InitializerInterface;

use Staffim\Behat\MailExtension\Context\MailAwareInterface;

class MailAwareInitializer implements InitializerInterface
{
    private $parameters;

    public function __construct(array $parameters)
    {
        $this->parameters = $parameters;
    }

    public function supports(ContextInterface $context)
    {
        return ($context instanceof MailAwareInterface);
    }

    public function initialize(ContextInterface $context)
    {
        $context->setMailAgent($this->mailAgent);
        $context->setParameters($this->parameters);
    }
}
