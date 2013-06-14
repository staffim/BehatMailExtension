<?php

namespace Staffim\Behat\MailExtension\Context;

/**
 * MailAgent aware interface for contexts.
 */
interface MailAwareInterface
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
