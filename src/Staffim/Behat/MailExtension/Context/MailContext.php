<?php

namespace Staffim\Behat\MailExtension\Context;

use Staffim\Behat\MailExtension\Exception\MailboxException;
use Staffim\Behat\MailExtension\Exception\MessageException;
use Staffim\Behat\MailExtension\Exception\MessageBodyFormatter;
use Staffim\Behat\MailExtension\Exception\PlainMessageFormatter;

/*
 * TODO Restore steps from previous version... With chained steps.
 *
 * See for details: https://github.com/Behat/Behat/issues/546
 */
class MailContext extends RawMailContext
{
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
     * @When /^(?:|I )remove mail messages from server$/
     */
    public function iRemoveMailMessagesFromServer()
    {
        $this->getMailAgent()->RemoveMessagesFromServer();
    }
}
