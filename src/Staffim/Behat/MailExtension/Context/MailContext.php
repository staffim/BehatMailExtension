<?php

namespace Staffim\Behat\MailExtension\Context;

use Behat\Gherkin\Node\PyStringNode;

use Staffim\Behat\MailExtension\Account;
use Staffim\Behat\MailExtension\Exception\MailboxException;
use Staffim\Behat\MailExtension\Exception\MessageException;
use Staffim\Behat\MailExtension\Exception\MessageBodyFormatter;
use Staffim\Behat\MailExtension\Exception\PlainMessageFormatter;

class MailContext extends RawMailContext
{
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

    /**
     * @Then /^(?:|I )should see (?P<count>\d+) new mail messag(e|es) after (?P<time>\d+) seconds$/
     */
    public function iShouldSeeNewMailMessagesAfterTime($count, $time)
    {
        if (!$this->getMailAgent()->wait($time * 1000, $count)) {
            throw new MailboxException(sprintf('Not found %s mail messages after %s seconds', $count, $time), $this->getMailAgent()->getMailbox());

        }
    }

    /**
     * @Then /^(?:|I )should see (?P<count>\d+) new mail messag(e|es) after waiting$/
     */
    public function iShouldSeeNewMailMessagesAfterWaiting($count)
    {
        $sleepTime = $this->getMailAgentParameter('max_duration');
        if (!$this->getMailAgent()->wait($sleepTime, $count)) {
            throw new MailboxException(sprintf('Not found %s mail messages after %s milliseconds', $count, $sleepTime), $this->getMailAgent()->getMailbox());
        }
    }

    /**
     * @Then /^(?:|I )should see (?P<count>\d+) messag(e|es)$/
     */
    public function iShouldSeeMailMessages($count)
    {
        $expectedCount = $count;
        $count         = $this->getMailAgent()->getMailbox()->getMessages()->count();

        if ($count !== (int) $expectedCount) {
            throw new MailboxException(sprintf('There are %s mail messages, not %s', $count, $expectedCount), $this->getMailAgent()->getMailbox());
        }
    }

    /**
     * @Then /^(?:|I )should see mail message with "(?P<text>(?:[^"]|\\")*)" in subject$/
     */
    public function iShouldSeeMailMessageWithTextInSubject($text)
    {
        $this->getMailAgent()->getMailbox()
            ->findBySubject($text)
            ->orThrow(new MailboxException(sprintf('Mail with "%s" in subject not found.', $text), $this->getMailAgent()->getMailbox()));
    }

    /**
     * @Then /^(?:|I )should see mail message with "(?P<address>(?:[^"]|\\")*)" in recipients$/
     */
    public function iShouldSeeMailMessageWithAddressInRecepients($address)
    {
        $this->getMailAgent()->getMailbox()
            ->findByRecipient($address)
            ->orThrow(new MailboxException(sprintf('Mail with "%s" in recipients not found.', $address), $this->getMailAgent()->getMailbox()));
    }

    /**
     * @Then /^(?:|I )should see mail message with subject "(?P<subject>(?:[^"]|\\")*)"$/
     */
    public function iShouldSeeMailMessageWithSubject($subject)
    {
        $this->getMailAgent()->getMailbox()
            ->findByEqualSubject($subject)
            ->orThrow(new MailboxException(sprintf('Mail with "%s" subject not found.', $subject), $this->getMailAgent()->getMailbox()));
    }

    /**
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" in mail message$/
     */
    public function iShouldSeeInMailMessage($text)
    {
        if (!$this->getMail()->findInBody($text)) {
            throw new MessageException(sprintf('Mail with "%s" in message body not found.', $text), $this->getMail(), new MessageBodyFormatter());
        }
    }

    /**
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" as reply address in mail message$/
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" sender address$/
     */
    public function iShouldSeeAsReplyAddress($text)
    {
        if (!$this->getMail()->findInFrom($text)) {
            throw new MessageException(sprintf('Mail with "%s" in address of message sender not found.', $text), $this->getMail(), new PlainMessageFormatter());
        }
    }

    /**
     * @Then /^(?:|I )should see attachment "(?P<text>(?:[^"]|\\")*)" in mail message$/
     */
    public function iShouldSeeAttachment($text)
    {
        if (!$this->getMail()->findInAttachment($text)) {
            throw new MessageException(sprintf('Mail with "%s" in attachment file name not found.', $text), $this->getMail());
        }
    }

    /**
     * @Then /^(?:|I )should see the date after "([^"]*)" in "([^"]*)" format$/
     */
    public function iShouldSeeTheDateAfterInFormat($arg1, $arg2)
    {
        $this->iShouldSeeInMailMessage((new \DateTime())->modify($arg1)->format($arg2));
    }

    /**
     * @When /^(?:|I )reply with:$/
     */
    public function iReplyWithTextMessage(PyStringNode $pystring)
    {
        $this->iReplyWithMessage($pystring->getRaw());
    }

    /**
     * @When /^(?:|I )remove mail messages from server$/
     */
    public function iRemoveMailMessagesFromServer()
    {
        $this->getMailAgent()->RemoveMessagesFromServer();
    }
}
