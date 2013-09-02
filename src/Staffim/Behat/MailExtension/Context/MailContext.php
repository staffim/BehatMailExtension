<?php

namespace Staffim\Behat\MailExtension\Context;

use Behat\Behat\Context\Step;
use Behat\Gherkin\Node\PyStringNode;
use Staffim\Behat\MailExtension\Exception\MailboxException,
    Staffim\Behat\MailExtension\Exception\MessageException;
use Staffim\Behat\MailExtension\Exception\MessageBodyFormatter;
use Staffim\Behat\MailExtension\Exception\PlainMessageFormatter;

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
        $sleepTime = $this->getMailAgentParameter('maxDuration');
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
     * @Then /^(?:|I )follow "(?P<linkPattern>(?:[^"]|\\")*)" from mail message$/
     */
    public function iFollowLinkInMailMessage($linkPattern)
    {
        $matches = $this->getMail()->findBodyMatches($linkPattern);

        if (empty($matches)) {
            throw new MessageException(sprintf('Not matches for pattern "%s" in message body.', $linkPattern), $this->getMail(), new MessageBodyFormatter);
        }

        return new Step\Given(sprintf('am on "%s"', $matches[2]));
    }

    /**
     * @Then /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" by pattern "(?P<pattern>(?:[^"]|\\")*)" from mail body$/
     */
    public function iFillInFromMailValue($field, $pattern)
    {
        $matches = $this->getMail()->findBodyMatches($pattern);

        if (empty($matches)) {
            throw new MessageException(sprintf('Not matches for pattern "%s"', $pattern), $this->getMail());
        }

        return new Step\Given(sprintf('fill in "%s" with "%s"', $field, $matches[1]));
    }

    /**
     * @Then /^(?:|I )should see "([^"]*)" base sender address$/
     * @Then /^(?:|я )должен видеть в адресе письма "([^"]*)"$/
     */
    public function iShouldSeeServerAddressInFrom($arg1)
    {
        return array(
            new Step\When(sprintf('should see "%s" as reply address in mail message', $this->getMailAgentParameter('baseAddress'))),
            new Step\When(sprintf('should see "%s" as reply address in mail message', $arg1))
        );
    }

    /**
     * @Given /^(?:|я )должен видеть в письме время часового пояса "([^"]*)" через (\d+) (?:дней|дня|день)$/
     */
    public function iShouldSeeCurrentHourInMail($arg1, $arg2)
    {
        $date = (new \DateTime($arg1))->modify("+$arg2 days")->format('d.m.Y H:');

        return new Step\When(sprintf('should see "%s" in mail message', $date));
    }

    /**
     * @Given /^(?:|я )отправляю ответ с текстом:$/
     */
    public function iReplyWithTextMessage(PyStringNode $pystring)
    {
        return new Step\Given('отправляю ответ с текстом "' . $pystring->getRaw() . '"');
    }
}
