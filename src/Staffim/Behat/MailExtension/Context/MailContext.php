<?php

namespace Staffim\Behat\MailExtension\Context;

use Staffim\Behat\MailExtension\Exception\MailboxException;
use Staffim\Behat\MailExtension\Exception\MessageBodyFormatter;
use Staffim\Behat\MailExtension\Exception\PlainMessageFormatter;

use function Staffim\Behat\MailExtension\X\message;
use function Staffim\Behat\MailExtension\X\mailbox;

/**
 * General context to work with incoming mail (over POP3).
 */
class MailContext extends RawMailContext
{
    /**
     * @Given my inbox is :address
     * @When /^(?:|I )go to "(?P<address>(?:[^"]|\\")*)" inbox$/
     */
    public function myMailboxIs($address)
    {
        $this->mailbox = $this->getMailAgent()->getMailboxFor($address);
    }

    /**
     * @When /^(?:|I )go to "(?P<subject>(?:[^"]|\\")*)" mail message$/
     */
    public function iGoToMailMessage($subject)
    {
        $this->message = $this->getMailbox()
            ->findLastBySubject($subject)
            ->getOrThrow(new MailboxException(
                sprintf('Mail with "%s" in subject not found.', $subject),
                $this->getMailbox()
            ));
    }

    /**
     * @When /^(?:|I )go to mail message with "(?P<address>(?:[^"]|\\")*)" in recipients$/
     */
    public function iGoToMailMessageWithInRecipients($address)
    {
        $this->message = $this->getMailbox()
            ->findLastByRecipient($address)
            ->getOrThrow(new MailboxException(
                sprintf('Mail with "%s" in recipients not found', $address),
                $this->getMailbox()
            ));
    }

    /**
     * @Then /^(?:|I )should see mail message with "(?P<text>(?:[^"]|\\")*)" in subject$/
     */
    public function iShouldSeeMailMessageWithTextInSubject($text)
    {
        $this->expect(
            mailbox()->getBySubject($text),
            sprintf('Mail with "%s" in subject not found.', $text)
        );
    }

    /**
     * @Then /^(?:|I )should see mail message with "(?P<address>(?:[^"]|\\")*)" in recipients$/
     */
    public function iShouldSeeMailMessageWithAddressInRecipients($address)
    {
        $this->expect(
            mailbox()->findLastByRecipient($address)->isDefined(),
            sprintf('Mail with "%s" in recipients not found', $address)
        );
    }

    /**
     * @Then /^(?:|I )should see mail message with subject "(?P<subject>(?:[^"]|\\")*)"$/
     */
    public function iShouldSeeMailMessageWithSubject($subject)
    {
        $this->expect(
            mailbox()->getByConcreteSubject($subject),
            sprintf('Mail with "%s" subject not found.', $subject)
        );
    }

    /**
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" in mail message$/
     */
    public function iShouldSeeInMailMessage($text)
    {
        $this->expectMessage(
            message()->findInBody($text),
            sprintf('Mail with "%s" in message body not found.', $text),
            new MessageBodyFormatter()
        );
    }

    /**
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" as reply address in mail message$/
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" sender address$/
     */
    public function iShouldSeeAsReplyAddress($text)
    {
        $this->expectMessage(
            message()->findInFrom($text),
            sprintf('Mail with "%s" in address of message sender not found.', $text),
            new PlainMessageFormatter()
        );
    }

    /**
     * Do we really need this check?..
     *
     * @Then /^(?:|I )should see at least (?P<number>\d+) mail messag(e|es)$/
     */
    public function iShouldSeeNewMailMessages($number)
    {
        if (!$this->getMailbox()->waitForSize($number, $this->getMailAgentParameter('max_duration'))) {
            throw new MailboxException(
                sprintf("Mailbox doesn't have %s messages.", $number),
                $this->getMailbox()
            );
        }
    }
}
