<?php

namespace Staffim\Behat\MailExtension\Context;

use Behat\Behat\Context\TranslatableContext;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Testwork\Tester\Result\TestResult;
use Staffim\Behat\MailExtension\Account;
use Staffim\Behat\MailExtension\Context\MailAwareInterface;
use Staffim\Behat\MailExtension\Exception\MailboxException;
use Staffim\Behat\MailExtension\MailAgent;
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
    private $mail;

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
     * Return parameter provided for MailAgent.
     *
     * @param string $key
     * @return mixed
     */
    public function getMailAgentParameter($key)
    {
        if (!array_key_exists($key, $this->mailAgentParameters)) {
            throw new \InvalidArgumentException("Parameter doesn't exist");
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

            // FIXME Repair this code for Behat 3.
            $eventTitle = explode('features/', $scenario->getFile() . ':' . $scenario->getLine())[1];
            $eventTitle = str_replace(['/', '\\'], '.', $eventTitle);

            $mailTo = $this->getMail()->getTo();
            $fileName = $eventTitle . $mailTo[0] . ':' . str_replace(['/', '\\'], '.', $this->getMail()->getSubject() . '.html');

            file_put_contents($this->getMailAgentParameter('failed_mail_dir') . $fileName, $this->getMail()->getRawParsedMessage());
        }
    }

    /**
     * @When /^(?:|I )sign in to "(?P<mailServer>[^"]*)" smtp server with "(?P<login>[^"]*)" and "(?P<password>[^"]*)"$/
     */
    // TODO Refactor: extract port to separate parameter, think about whole step.
    public function iSignInToSmtpServer($mailServer, $login, $password)
    {
        list($mailServer, $port) = explode(':', $mailServer) + [null, null];

        $smtpAccount = new Account($mailServer, $port, $login, $password);
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
            $filename = $this->getMailAgentParameter('files_path') . $filename;
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
     * @When /^(?:|I )send mail from file "(?P<filename>(?:[^"]|\\")*)"$/
     */
    public function iSendMailFromFile($filename)
    {
        $mail = $this->getMailAgent()->createMessageFromFile($this->getMailAgentParameter('files_path') . $filename);
        $this->getMailAgent()->send($mail);
    }

    /**
     * @When /^(?:|I )reply with message from file "(?P<filename>(?:[^"]|\\")*)"$/
     */
    public function iReplyWithMessageFromFile($filename)
    {
        $mail = $this->getMailAgent()->createReplyMessageFromFile($this->getMail()->getRawMail(), $this->getMailAgentParameter('files_path') . $filename);
        $this->getMailAgent()->send($mail);
    }

    /**
     * @When /^(?:|I )go to "(?P<subject>(?:[^"]|\\")*)" mail message$/
     */
    public function iGoToMailMessage($subject)
    {
        $this->mail = $this->getMailAgent()->getMailbox()
            ->findBySubject($subject)
            ->orThrow(new MailboxException(sprintf('Mail with "%s" in subject text not found.', $subject), $this->getMailAgent()->getMailbox()));
    }

    /**
     * @When /^(?:|I )go to mail message with "(?P<address>(?:[^"]|\\")*)" in recipients$/
     */
    public function iGoToMailMessageWithInRecipients($address)
    {
        $this->mail = $this->getMailAgent()->getMailbox()
            ->findByRecipient($address)
            ->orThrow(new MailboxException(sprintf('Mail with "%s" in recipient addresses not found', $address), $this->getMailAgent()->getMailbox()));
    }

    /**
     * @When /^(?:|I )receive mail messages$/
     */
    public function iReceiveMailMessages()
    {
        $this->getMailAgent()->receive();
    }
}
