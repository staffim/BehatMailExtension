<?php

namespace Staffim\Behat\MailExtension\Context;

use Staffim\Behat\MailExtension\Exception\MailboxException;
use Staffim\Behat\MailExtension\Exception\MessageException;
use Staffim\Behat\MailExtension\Exception\MessageBodyFormatter;
use Staffim\Behat\MailExtension\Exception\PlainMessageFormatter;

/**
 * Only working with incoming mail.
 */
class MailContext extends RawMailContext
{
    /**
     * @Given my mailbox is :address
     */
    public function myMailboxIs($address)
    {
        $this->mailbox = $this->getMailAgent()->getMailboxFor($address);
    }

    /**
     * @Then /^(?:|I )should see mail message with "(?P<text>(?:[^"]|\\")*)" in subject$/
     */
    public function iShouldSeeMailMessageWithTextInSubject($text)
    {
        $messages = $this->getMailbox()->findBySubject($text);
        if (!count($messages)) {
            throw new MailboxException(sprintf('Mail with "%s" in subject not found.', $text), $this->getMailbox());
        }
    }

    /**
     * @When /^(?:|I )go to "(?P<subject>(?:[^"]|\\")*)" mail message$/
     */
    public function iGoToMailMessage($subject)
    {
        $this->mail = $this->getMailbox()
            ->lastBySubject($subject)
            ->getOrThrow(new MailboxException(
                sprintf('Mail with "%s" in subject text not found.', $subject),
                $this->getMailbox()
            ));
    }

    /**
     * @When /^(?:|I )go to mail message with "(?P<address>(?:[^"]|\\")*)" in recipients$/
     */
    public function iGoToMailMessageWithInRecipients($address)
    {
        $this->mail = $this->getMailbox()
            ->lastByRecipient($address)
            ->getOrThrow(new MailboxException(
                sprintf('Mail with "%s" in recipients not found', $address),
                $this->getMailbox()
            ));
    }

    /**
     * @Then /^(?:|I )should see mail message with "(?P<address>(?:[^"]|\\")*)" in recipients$/
     */
    public function iShouldSeeMailMessageWithAddressInRecepients($address)
    {
        $this->getMailbox()
            ->lastByRecipient($address)
            ->getOrThrow(new MailboxException(
                sprintf('Mail with "%s" in recipients not found', $address),
                $this->getMailbox()
            ));
    }

    /**
     * @Then /^(?:|I )should see mail message with subject "(?P<subject>(?:[^"]|\\")*)"$/
     */
    public function iShouldSeeMailMessageWithSubject($subject)
    {
        $messages = $this->getMailbox()->findByEqualSubject($subject);
        if (!count($messages)) {
            throw new MailboxException(sprintf('Mail with "%s" subject not found.', $subject), $this->getMailbox());
        }
    }

    /**
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" in mail message$/
     */
    public function iShouldSeeInMailMessage($text)
    {
        if (!$this->getMail()->findInBody($text)) {
            throw new MessageException(
                sprintf('Mail with "%s" in message body not found.', $text),
                $this->getMail(),
                new MessageBodyFormatter()
            );
        }
    }

    /**
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" as reply address in mail message$/
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" sender address$/
     */
    public function iShouldSeeAsReplyAddress($text)
    {
        if (!$this->getMail()->findInFrom($text)) {
            throw new MessageException(
                sprintf('Mail with "%s" in address of message sender not found.', $text),
                $this->getMail(),
                new PlainMessageFormatter()
            );
        }
    }

    /**
     * @Then /^(?:|I )should see "(?P<count>\d+)" new mail messag(e|es) after "(?P<time>\d+)" seconds$/
     */
    public function iShouldSeeNewMailMessagesAfterTime($count, $time)
    {
        $this->waitForMessages($count, $time * 1000);
    }

    /**
     * @Then /^(?:|I )should see "(?P<count>\d+)" new mail messag(e|es) after some waiting$/
     */
    public function iShouldSeeNewMailMessagesAfterWaiting($count)
    {
        $this->waitForMessages($count, $this->getMailAgentParameter('max_duration'));
    }

    /**
     * @Then /^(?:|I )should see new mail message after some waiting$/
     */
    public function iShouldSeeNewMailMessageAfterWaiting()
    {
        $this->waitForMessages(1, $this->getMailAgentParameter('max_duration'));
    }

    private function waitForMessages($size, $sleepTime)
    {
        if (!$this->getMailbox()->waitForSize($size, $sleepTime)) {
            throw new MailboxException(
                sprintf('Not found %s mail messages after %s milliseconds', $size, $sleepTime),
                $this->getMailbox()
            );
        }
    }
}
