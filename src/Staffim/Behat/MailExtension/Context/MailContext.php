<?php

namespace Staffim\Behat\MailExtension\Context;

use Behat\Behat\Context\Step;

class MailContext extends RawMailContext
{

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        // Initialize your context here
        $this->context = $parameters;
    }

    /**
     * @Then /^(?:|I )should see (?P<count>\d+) new mail messag(e|es)$/
     * @Then /^(?:|я )должен видеть (?P<count>\d+) нов(ых|ое) пис(ем|ьма|ьмо)$/
     */
    public function iShouldSeeNewMailMessages($count)
    {
        $expectedCount = $count;
        $count         = $this->getMailAgent()->getMailbox()->getMessages()->count();

        if ($count !== (int) $expectedCount) {
            // TODO Split message to short (default exception message) and detail description.
            throw new \Exception(
                "There are $count mail messages, not $expectedCount\n"
                    . $this->getMailAgent()->getMailbox()->getMailFromToSubject()
            );
        }
    }

    /**
     * @Then /^(?:|I )should see mail message with "(?P<text>(?:[^"]|\\")*)" in subject$/
     * @Then /^(?:|я )должен видеть письмо с "(?P<text>(?:[^"]|\\")*)" в теме$/
     */
    public function iShouldSeeMailMessageWithTextInSubject($text)
    {
        $this->getMailAgent()->getMailbox()
            ->findBySubject($text)
            ->orThrow(new \Exception('Message with "' . $text . '" in subject not found.'));
    }

    /**
     * @Then /^(?:|I )should see mail message with subject "(?P<subject>(?:[^"]|\\")*)"$/
     * @Then /^(?:|я )должен видеть письмо с темой "(?P<subject>(?:[^"]|\\")*)"$/
     */
    public function iShouldSeeMailMessageWithSubject($subject)
    {
        $this->getMailAgent()->getMailbox()
            ->findByEqualSubject($subject)
            ->orThrow(new \Exception('Message with "' . $subject . '" subject not found.'));
    }

    /**
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" in mail message$/
     * @Then /^(?:|я )должен видеть "(?P<text>(?:[^"]|\\")*)" в письме$/
     */
    public function iShouldSeeInMailMessage($text)
    {
        if (!$this->getMail()->findInBody($text)) {
            // TODO Split message to short (default exception message) and detail description.
            throw new \Exception("Mail with \"$text\" in message body not found.\n" . $this->getMail()->getPlainMessage());
        }
    }

    /**
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" as reply address in mail message$/
     * @Then /^(?:|я )должен видеть "(?P<text>(?:[^"]|\\")*)" в адресе отправителя письма$/
     */
    public function iShouldSeeAsReplyAddress($text)
    {
        if (!$this->getMail()->findInFrom($text)) {
            throw new \Exception("Mail with $text in address of message sender not found\n" . $this->getMail()->getPlainMessage());
        }
    }

    /**
     * @Then /^(?:|I )should see attachment "(?P<text>(?:[^"]|\\")*)" in mail message$/
     * @Then /^(?:|я )должен видеть прикрепленный к письму файл "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function iShouldSeeAttachment($text)
    {
        if (!$this->getMail()->findInAttachment($text)) {
            throw new \Exception(sprintf('Mail with "%s" in attachment file name not found.', $text));
        }
    }

    /**
     * @Then /^(?:|I )follow "(?P<linkPattern>(?:[^"]|\\")*)" from mail message$/
     * @Then /^(?:|я )перехожу по ссылке "(?P<linkPattern>(?:[^"]|\\")*)" из письма$/
     */
    public function iFollowLinkInMailMessage($linkPattern)
    {
        $matches = $this->getMail()->findBodyMatches($linkPattern);

        return new Step\Given(sprintf('am on "%s"', $matches[2]));
    }

    /**
     * @Then /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" by pattern "(?P<pattern>(?:[^"]|\\")*)" from mail body$/
     * @Then /^(?:|я )заполняю поле "(?P<field>(?:[^"]|\\")*)" по шаблону "(?P<pattern>(?:[^"]|\\")*)" из тела письма$/
     */
    public function iFillInFromMailValue($field, $pattern)
    {
        $matches = $this->getMail()->findBodyMatches($pattern);

        return new Step\Given(sprintf('fill in "%s" with "%s"', $field, $matches[1]));
    }
}
