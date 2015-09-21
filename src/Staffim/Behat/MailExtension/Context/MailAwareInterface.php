<?php

namespace Staffim\Behat\MailExtension\Context;

use Behat\Behat\Context\Context;
use Staffim\Behat\MailExtension\MailAgent;

/**
 * MailAgent aware interface for contexts.
 */
interface MailAwareInterface extends Context
{
    /**
     * @param MailAgent $mailAgent
     * @return mixed
     */
    public function setMailAgent(MailAgent $mailAgent);

    /**
     * Sets parameters provided for MailAgent.
     *
     * @param array $parameters
     */
    public function setMailAgentParameters(array $parameters);
}
