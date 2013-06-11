<?php

namespace Staffim\Behat\MailExtension\Context;

use Behat\Behat\Context\Step;
use Behat\Behat\Context\BehatContext;
use Behat\Behat\Context\TranslatedContextInterface;
use Staffim\Behat\MailExtension\Context\MailAwareInterface;

class RawMailContext extends BehatContext implements MailAwareInterface
//    implements TranslatedContextInterface
{
//    public function getTranslationResources()
//    {
//        return glob(__DIR__ . '/../../../../i18n/*.xliff');
//    }

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

//    /**
//     * Выход с почтового сервера при сценарии с почтой
//     *
//     * @AfterScenario @mail
//     */
//    public function afterFailedMail(ScenarioEvent $event)
//    {
//        if ($this->hasMailbox()) {
//            $this->iSignOutMailServer();
//        };
//    }

    /**
     * @var \Staffim\Behat\MailExtension\Message
     */
    private $mail;

    /**
     * @var \Staffim\Behat\MailExtension\Mailbox
     */
    private $mailbox;

    /**
     * @return \Staffim\Behat\MailExtension\Message
     *
     * @throws \Exception
     */
    // TODO To option.
    private function getMail()
    {
        if (!$this->mail) {
            throw new \Exception('Mail not open yet.');
        }

        return $this->mail;
    }

    /**
     * @return \Staffim\Behat\MailExtension\Mailbox
     *
     * @throws \Exception
     */
    // TODO To option.
    private function getMailAgent()
    {
        if (!$this->mailbox) {
            throw new \Exception('Mailbox not created');
        }

        return $this->mailAgent;
    }

    /**
     * @return \Staffim\Behat\MailExtension\Mailbox
     */
    public function hasMailbox()
    {
        return !$this->mailbox;
    }

    /**
     * @When /^(?:|I )sign in to "(?P<mailServer>[^"]*)" mail server with "(?P<user>[^"]*)" and "(?P<password>[^"]*)"$/
     * @When /^(?:|я )авториз(уюсь|овался) на "(?P<mailServer>[^"]*)" почтовом сервере с "(?P<user>[^"]*)" и "(?P<password>[^"]*)"$/
     */
    public function iSignInToMailServer($mailServer, $user, $password)
    {
        $this->mailbox = new Mailbox($mailServer, $user, $password);
    }

    /**
     * @When /^(?:|I )sign out mail server$/
     * @When /^(?:|я )выхожу с почтового сервера$/
     */
    // TODO "I sign out FROM mail server".
    public function iSignOutMailServer()
    {
        if ($this->hasMailbox()) {
            $this->getMailbox()->disconnect();
            $this->mailbox = null;
        }
    }

    /**
     * @Then /^(?:|I )should see (?P<count>\d+) new mail messag(e|es)$/
     * @Then /^(?:|я )должен видеть (?P<count>\d+) нов(ых|ое) пис(ем|ьма|ьмо)$/
     */
    public function iShouldSeeNewMailMessages($count)
    {
        $expectedCount = $count;
        $count         = $this->getMailbox()->getMails()->count();

        if ($count !== (int) $expectedCount) {
            // TODO Split message to short (default exception message) and detail description.
            throw new \Exception("You have $count mail messages:\n" . $this->getMailbox()->getMailInfo());
        }
    }

    /**
     * @Then /^(?:|I )should see mail message with "(?P<text>(?:[^"]|\\")*)" in subject$/
     * @Then /^(?:|я )должен видеть письмо с "(?P<text>(?:[^"]|\\")*)" в теме$/
     */
    public function iShouldSeeMailMessageWithTextInSubject($text)
    {
        $this->getMailbox()
            ->findBySubject($text)
            ->orThrow(new \Exception(sprintf('Mail with "%s" in subject not found.', $text)));
    }

    /**
     * @Then /^(?:|I )should see mail message with subject "(?P<subject>(?:[^"]|\\")*)"$/
     * @Then /^(?:|я )должен видеть письмо с темой "(?P<subject>(?:[^"]|\\")*)"$/
     */
    public function iShouldSeeMailMessageWithSubject($subject)
    {
        $this->getMailbox()->findByEqualSubject($subject)->
            orThrow(new \Exception(sprintf('Mail with "%s" subject not found.', $subject)));
    }

    /**
     * @When /^(?:|I )delete mails from mailbox$/
     * @When /^(?:|я )удал(яю|ил) письма из почтового ящика$/
     *
     * Below are deprecated.
     *
     * @When /^(?:|I )clean mailbox$/
     * @When /^(?:|я )очи(щаю|стил) почтовый ящик$/
     */
    public function iDeleteMailFromMailbox()
    {
        $this->getMailbox()->deleteMail();
    }

    /**
     * @Given /^(?:|I )vanishes "(?P<mailServer>[^"]*)" with "(?P<login>[^"]*)" and "(?P<password>[^"]*)"$/
     * @Given /^(?:|я )(сбрасываю|сбросил) "(?P<mailServer>[^"]*)" (|почтовый сервер) с "(?P<login>[^"]*)" и "(?P<password>[^"]*)"$/
     */
    // TODO Remove — should be two steps (connect and delete) instead.
    public function iCleanMailServer($mailServer, $login, $password)
    {
        Mailbox::resetMailbox($mailServer, $login, $password);
    }

    /**
     * @When /^(?:|I )go to "(?P<subject>(?:[^"]|\\")*)" mail message$/
     * @When /^(?:|я )открываю письмо "(?P<subject>(?:[^"]|\\")*)"$/
     */
    public function iGoToMailMessage($subject)
    {
        $this->mail = $this->getMailbox()
            ->findBySubject($subject)
            // TODO Split message to short (default exception message) and detail description.
            ->orThrow(new \Exception("Mail with $subject in subject text not found.\nMessages:\n" . $this->getMailbox()->getMailInfo()));
    }

    /**
     * @When /^(?:|I )go to recipient "(?P<address>(?:[^"]|\\")*)" mail message$/
     * @When /^(?:|я )открываю письмо у получателя "(?P<address>(?:[^"]|\\")*)"$/
     */
    // TODO I go to mail message with "<address>" in recipients, Я открываю письмо, адресованное "<address>"
    public function iGoToMailMessageByRecipientAddress($address)
    {
        $this->mail = $this->getMailbox()
            ->findByRecipient($address)
            // TODO Split message to short (default exception message) and detail description.
            ->orThrow(new \Exception('Mail with "$address" in recipient addresses not found'."\nMessages:\n" . $this->getMailbox()->getMailInfo()));
    }

    /**
     * @throws \Exception If no matches found.
     *
     * @param $pattern
     *
     * @return string[]
     */
    private function findBodyMatches($pattern)
    {
        $body = $this->getMail()->getBody();

        preg_match($pattern, $body, $matches);
        if (empty($matches)) {
            // TODO Split message to short (default exception message) and detail description.
            throw new \Exception(sprintf('Not matches for pattern "%s" in message body: %s', $pattern, $body));
        }

        return $matches;
    }

    /**
     * @Then /^(?:|I )follow "(?P<linkPattern>(?:[^"]|\\")*)" from mail message$/
     * @Then /^(?:|я )перехожу по ссылке "(?P<linkPattern>(?:[^"]|\\")*)" из письма$/
     */
    public function iFollowLinkInMailMessage($linkPattern)
    {
        $matches = $this->findBodyMatches($linkPattern);

        return new Step\Given(sprintf('am on "%s"', $matches[2]));
    }

    /**
     * @Then /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" by pattern "(?P<pattern>(?:[^"]|\\")*)" from mail body$/
     * @Then /^(?:|я )заполняю поле "(?P<field>(?:[^"]|\\")*)" по шаблону "(?P<pattern>(?:[^"]|\\")*)" из тела письма$/
     */
    public function iFillInFromMailValue($field, $pattern)
    {
        $matches = $this->findBodyMatches($pattern);

        return new Step\Given(sprintf('fill in "%s" with "%s"', $field, $matches[1]));
    }

    /**
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" in mail message$/
     * @Then /^(?:|я )должен видеть "(?P<text>(?:[^"]|\\")*)" в письме$/
     */
    public function iShouldSeeInMailMessage($text)
    {
        if (!$this->getMail()->findInBody($text)) {
            // TODO Split message to short (default exception message) and detail description.
            throw new \Exception("Mail with \"$text\" in message body not found.\nPlain message:\n" . $this->getMail()->getPlainMessage());
        }
    }

    /**
     * @Then /^(?:|I )should see "(?P<text>(?:[^"]|\\")*)" as reply address in mail message$/
     * @Then /^(?:|я )должен видеть "(?P<text>(?:[^"]|\\")*)" в адресе отправителя письма$/
     */
    public function iShouldSeeAsReplyAddress($text)
    {
        if (!$this->getMail()->findInFrom($text)) {
            throw new \Exception("Mail with $text in address of message sender not found\nPlain message:\n" . $this->getMail()->getPlainMessage());
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
     * @When /^(?:|I )reply with "(?P<text>(?:[^"]|\\")*)" to mail server "(?P<mailServer>(?:[^"]|\\")*)"$/
     * @When /^(?:|я )отправляю ответ с текстом "(?P<text>(?:[^"]|\\")*)" на сервер "(?P<mailServer>(?:[^"]|\\")*)"$/
     */
    public function iReplyWithToMailMessage($text, $mailServer)
    {
        $this->getMail()->reply($mailServer, $text);
    }

    /**
     * @When /^(?:|I )send mail to server "(?P<mailServer>(?:[^"]|\\")*)" with subject "(?P<subject>(?:[^"]|\\")*)" and body "(?P<body>(?:[^"]|\\")*)" to address "(?P<to>(?:[^"]|\\")*)" from "(?P<from>(?:[^"]|\\")*)"$/
     * @When /^(?:|я )отправляю письмо серверу "(?P<mailServer>(?:[^"]|\\")*)" с темой "(?P<subject>(?:[^"]|\\")*)" и текстом "(?P<body>(?:[^"]|\\")*)" по адресу "(?P<to>(?:[^"]|\\")*)" от "(?P<from>(?:[^"]|\\")*)"$/
     */
    public function iSendMail($mailServer, $subject, $body, $to, $from)
    {
        Message::createAndSendTo($mailServer, $subject, $body, $to, $from);
    }
}
