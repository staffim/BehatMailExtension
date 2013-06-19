<?php

namespace Staffim\Behat\MailExtension\Context;

use Behat\Behat\Context\Step;
use Behat\Behat\Context\BehatContext;
use Behat\Behat\Context\TranslatedContextInterface;
use Staffim\Behat\MailExtension\Account;
use Staffim\Behat\MailExtension\Context\MailAwareInterface;
use Staffim\Behat\MailExtension\MailAgent;
use Staffim\Behat\MailExtension\Message;

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
     */
    public function iReplyWithMessage($text)
    {
        $replyMail = $this->getMailAgent()->createReplyMessage($this->getMail()->getRawMail(), $text);
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
