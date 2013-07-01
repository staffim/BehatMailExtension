<?php

namespace Staffim\Behat\MailExtension\Context;

use Behat\Behat\Context\Step;
use Behat\Behat\Context\BehatContext;
use Behat\Behat\Context\TranslatedContextInterface;
use Behat\Behat\Event\ScenarioEvent;
use Behat\Behat\Event\StepEvent;
use Behat\Behat\Formatter\FailedScenariosFormatter;
use Staffim\Behat\MailExtension\Account;
use Staffim\Behat\MailExtension\Context\MailAwareInterface;
use Staffim\Behat\MailExtension\MailAgent;
use Staffim\Behat\MailExtension\Message;
use Symfony\Component\Config\Definition\Exception\Exception;

class RawMailContext extends BehatContext implements MailAwareInterface, TranslatedContextInterface
{
    public function getTranslationResources()
    {
        return glob(__DIR__ . '/../../../../../i18n/ru.xliff');
    }

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
    private $mail;

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
    public function getMailAgent()
    {
        return $this->mailAgent;
    }

    /**
     * @return Message
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Return parameters provided for MailAgent.
     *
     * @return array $parameters
     */
    public function getMailAgentParameters()
    {
        return $this->mailAgentParameters;
    }

    /**
     * Return parameters provided for MailAgent.
     *
     * @param string $key
     * @throws \Symfony\Component\Config\Definition\Exception\Exception
     * @return mixed $parameter
     */
    public function getMailAgentParameter($key)
    {
        if (!isset($this->mailAgentParameters[$key])) {
            throw new Exception("Parameter doesn't exist");
        }

        return $this->mailAgentParameters[$key];
    }

    /**
     * @AfterScenario
     */
    public function saveMailMessageAfterFail(ScenarioEvent $event) {
        if ($this->getMailAgentParameter('failedMailDir') && ($event->getResult() === StepEvent::FAILED) && $this->getMail()) {
            $scenario = $event->getScenario();

            $eventTitle = explode('features/', $scenario->getFile() . ':' . $scenario->getLine())[1];
            $eventTitle = str_replace(['/', '\\'], '.', $eventTitle);

            $mailTo = $this->getMail()->getTo();
            $fileName = $eventTitle . $mailTo[0] . ':' . str_replace(['/', '\\'], '.', $this->getMail()->getSubject());

            file_put_contents($this->getMailAgentParameter('failedMailDir') . $fileName, $this->getMail()->getRawParsedMessage());
        }
    }

    /**
     * @When /^(?:|I )sign in to "(?P<mailServer>[^"]*)" smtp server with "(?P<login>[^"]*)" and "(?P<password>[^"]*)"$/
     */
    public function iSignInToSmtpServer($mailServer, $login, $password)
    {
        $smtpAccount = new Account($mailServer, $login, $password);
        $this->getMailAgent()->connectSmtpServer($smtpAccount);
    }

    /**
     * @When /^(?:|I )sign out from mail server$/
     */
    public function iSignOutFromMailServer()
    {
        $this->getMailAgent()->disconnect();
    }

    /**
     * @When /^(?:|I )remove mail messages$/
     */
    public function iRemoveMailMessages()
    {
        $this->getMailAgent()->removeMessages();
    }

    /**
     * @When /^(?:|I )reply with "(?P<text>(?:[^"]|\\")*)"$/
     * @When /^(?:|I )reply with "(?P<text>(?:[^"]|\\")*)" and attach "(?P<filename>(?:[^"]|\\")*)"$/
     */
    public function iReplyWithMessage($text, $filename = null)
    {
        if ($filename) {
            $filename = $this->getMailAgentParameter('filesPath') . $filename;
        }
        $replyMail = $this->getMailAgent()->createReplyMessage($this->getMail()->getRawMail(), $text, $filename);
        $this->getMailAgent()->send($replyMail);
    }

    /**
     * @When /^(?:|I )send mail with subject "(?P<subject>(?:[^"]|\\")*)" and body "(?P<body>(?:[^"]|\\")*)" to address "(?P<to>(?:[^"]|\\")*)" from "(?P<from>(?:[^"]|\\")*)"$/
     */
    public function iSendMail($subject, $body, $to, $from)
    {
        $mail = $this->getMailAgent()->createMessage($subject, $body, $from, $to);
        $this->getMailAgent()->send($mail);
    }

    /**
     * @When /^(?:|I )go to "(?P<subject>(?:[^"]|\\")*)" mail message$/
     */
    public function iGoToMailMessage($subject)
    {
        $this->mail = $this->getMailAgent()->getMailbox()
            ->findBySubject($subject)
            // TODO Split message to short (default exception message) and detail description.
            ->orThrow(new \Exception("Mail with $subject in subject text not found.\nMessages:\n" . $this->getMailAgent()->getMailbox()->getMailFromToSubject()));
    }

    /**
     * @When /^(?:|I )go to mail message with "(?P<address>(?:[^"]|\\")*)" in recipients$/
     */
    public function iGoToMailMessageWithInRecipients($address)
    {
        $this->mail = $this->getMailAgent()->getMailbox()
            ->findByRecipient($address)
            // TODO Split message to short (default exception message) and detail description.
            ->orThrow(new \Exception('Mail with "$address" in recipient addresses not found'."\nMessages:\n" . $this->getMailAgent()->getMailbox()->getMailFromToSubject()));
    }

    /**
     * @When /^(?:|I )receive mail messages$/
     */
    public function iReceiveMailMessages()
    {
        $this->getMailAgent()->receive();
    }
}
