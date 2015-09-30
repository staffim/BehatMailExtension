<?php

namespace Staffim\Behat\MailExtension\Context;

use InvalidArgumentException;
use PhpOption\Option;
use Behat\Behat\Context\TranslatableContext;
use Behat\Behat\Hook\Scope\ScenarioScope;
use Behat\Testwork\Tester\Result\TestResult;
use Staffim\Behat\MailExtension\Exception\Exception;
use Staffim\Behat\MailExtension\Exception\MailboxException;
use Staffim\Behat\MailExtension\Exception\MessageException;
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
    protected $message;

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
     * @deprecated Use getMessage() instead.
     *
     * @return Message
     */
    protected function getMail()
    {
        return $this->message;
    }

    /**
     * @return Option
     */
    protected function getMessage()
    {
        return Option::fromValue($this->message);
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
     * @param callable $checker
     * @param \Exception|string $exception
     */
    protected function expect($checker, $exception)
    {
        if (!$this->getMailbox()->waitFor($checker, $this->getMailAgentParameter('max_duration'))) {
            if (is_object($exception) && ($exception instanceof \Exception)) {
                throw $exception;
            } else {
                throw new MailboxException((string)$exception, $this->getMailbox());
            }
        }
    }

    /**
     * @param callable $checker
     * @param \Exception|string $exception
     * @param callable $formatter Optional formatter for MessageException.
     */
    protected function expectMessage($checker, $exception, $formatter = null)
    {
        /** @var Message $message */
        $message = $this->getMessage()->getOrThrow(new Exception('Mail message is not defined.'));

        if (!$checker($message)) {
            if (is_object($exception) && ($exception instanceof \Exception)) {
                throw $exception;
            } else {
                throw new MessageException((string)$exception, $message, $formatter);
            }
        }
    }

    /**
     * @AfterScenario
     *
     * @param ScenarioScope $event
     */
    public function tryToSaveMessage(ScenarioScope $event)
    {
        if (
            $this->getMailAgentParameter('failed_mail_dir')
            // Only for failed scenarios.
            && ($event->getTestResult()->getResultCode() === TestResult::FAILED)
        ) {
            $this->getMessage()->forAll(function (Message $message) use ($event) {
                $eventTitle = explode('features/', $event->getFeature()->getFile() . ':' . $event->getScenario()->getLine())[1];
                $eventTitle = str_replace(['/', '\\'], '.', $eventTitle);

                $mailTo = $message->getTo();
                // TODO HTML?
                $fileName = $eventTitle . $mailTo[0] . ':' . str_replace(['/', '\\'], '.', $message->getSubject() . '.html');

                file_put_contents($this->getMailAgentParameter('failed_mail_dir') . $fileName, $message->toRaw());
            });
        }
    }
}
