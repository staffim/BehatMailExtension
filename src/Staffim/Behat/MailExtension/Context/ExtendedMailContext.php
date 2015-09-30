<?php

namespace Staffim\Behat\MailExtension\Context;

use Staffim\Behat\MailExtension\Account;
use Staffim\Behat\MailExtension\Exception\MailboxException;
use Staffim\Behat\MailExtension\Exception\MessageException;

/**
 * Additional steps for sending mail. Sending is using less than retrieving mail.
 */
// TODO This class may not works correctly for now. Fix it.
class ExtendedMailContext extends RawMailContext
{
    /**
     * @When /^(?:|I )sign in to "(?P<mailServer>[^"]*)" smtp server with "(?P<login>[^"]*)" and "(?P<password>[^"]*)"$/
     */
    // TODO Refactor: extract port to separate parameter, think about whole step.
    public function iSignInToSmtpServer($mailServer, $login, $password)
    {
        list($mailServer, $port) = explode(':', $mailServer) + [null, null];

        $smtpAccount = new Account($mailServer, $port, $login, $password);
        $this->getMailAgent()->setSmtpAccount($smtpAccount);
    }

    /**
     * @When /^(?:|I )sign out from mail server$/
     */
    public function iSignOutFromMailServer()
    {
        $this->getMailAgent()->disconnect();
    }

    /**
     * @When /^(?:|I )remove mail from the server$/
     */
    public function iRemoveMailMessages()
    {
        // TODO Implement.
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
     * @Then /^(?:|I )should see "(?P<count>\d+)" messag(e|es)$/
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
     * @Then /^(?:|I )should see attachment "(?P<text>(?:[^"]|\\")*)" in mail message$/
     */
    public function iShouldSeeAttachment($text)
    {
        if (!$this->getMail()->findInAttachment($text)) {
            throw new MessageException(sprintf('Mail with "%s" in attachment file name not found.', $text), $this->getMail());
        }
    }
}
