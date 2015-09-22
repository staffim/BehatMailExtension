<?php

namespace Staffim\Behat\MailExtension\Context;

use Behat\Behat\Context\TranslatableContext;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Testwork\Tester\Result\TestResult;
use InvalidArgumentException;
use Staffim\Behat\MailExtension\MailAgent;
use Staffim\Behat\MailExtension\Mailbox;
use Staffim\Behat\MailExtension\Message;

class RawMailContext implements MailAwareInterface, TranslatableContext
{
    /**
     * @var MailAgent
     */
    private $mailAgent;

    /**
     * @var array
     */
    private $mailAgentParameters;

    /**
     * @var Message
     */
    protected $mail;

    /**
     * @var Mailbox
     */
    protected $mailbox;

    public static function getTranslationResources()
    {
        return glob(__DIR__ . '/../../../../../i18n/ru.xliff');
    }

    /**
     * @param MailAgent $mailAgent
     * @return mixed|void
     */
    public function setMailAgent(MailAgent $mailAgent)
    {
        $this->mailAgent = $mailAgent;
    }

    /**
     * Sets parameters provided for MailAgent.
     *
     * @param array $parameters
     */
    public function setMailAgentParameters(array $parameters)
    {
        $this->mailAgentParameters = $parameters;
    }

    /**
     * @return MailAgent
     */
    protected function getMailAgent()
    {
        return $this->mailAgent;
    }

    /**
     * @return Message
     */
    // TODO Use Option here.
    protected function getMail()
    {
        return $this->mail;
    }

    /**
     * @return Mailbox
     */
    protected function getMailbox()
    {
        if (!$this->mailbox) {
            $this->mailbox = $this->mailAgent->getMailbox();
        }

        return $this->mailbox;
    }

    /**
     * Return parameter provided for MailAgent.
     *
     * @throws InvalidArgumentException
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getMailAgentParameter($key)
    {
        if (!array_key_exists($key, $this->mailAgentParameters)) {
            throw new InvalidArgumentException("Parameter doesn't exist");
        }

        return $this->mailAgentParameters[$key];
    }

    /**
     * @AfterScenario
     *
     * @param AfterScenarioScope $event
     */
    public function saveMailMessageAfterFail(AfterScenarioScope $event) {
        if (
            $this->getMailAgentParameter('failed_mail_dir')
            && ($event->getTestResult()->getResultCode() === TestResult::FAILED)
            && $this->getMail()
        ) {
            $scenario = $event->getScenario();

            // TODO Repair this code for Behat 3.
            $eventTitle = explode('features/', $scenario->getFile() . ':' . $scenario->getLine())[1];
            $eventTitle = str_replace(['/', '\\'], '.', $eventTitle);

            $mailTo = $this->getMail()->getTo();
            $fileName = $eventTitle . $mailTo[0] . ':' . str_replace(['/', '\\'], '.', $this->getMail()->getSubject() . '.html');

            file_put_contents($this->getMailAgentParameter('failed_mail_dir') . $fileName, $this->getMail()->getRawParsedMessage());
        }
    }
}
