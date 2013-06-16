<?php

namespace Staffim\Behat\MailExtension\Context;

use Behat\Behat\Context\Step;
use Behat\Behat\Context\BehatContext;
use Behat\Behat\Context\TranslatedContextInterface;
use Staffim\Behat\MailExtension\Account;
use Staffim\Behat\MailExtension\Context\MailAwareInterface;
use Staffim\Behat\MailExtension\MailAgent;
use Staffim\Behat\MailExtension\Message;

class RawMailContext extends BehatContext implements MailAwareInterface
//    implements TranslatedContextInterface
{
//    public function getTranslationResources()
//    {
//        return glob(__DIR__ . '/../../../../i18n/*.xliff');
//    }

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

//     /**
//     * Выход с почтового сервера при сценарии с почтой
//     *
//     * @AfterScenario @mail
//     */
//    public function afterMailScenario()
//    {
//        $this->getMailAgent()->disconnect();
//    }

    /**
     * @When /^(?:|I )sign in to "(?P<mailServer>[^"]*)" smtp server with "(?P<user>[^"]*)" and "(?P<password>[^"]*)"$/
     * @When /^(?:|я )авториз(уюсь|овался) на "(?P<mailServer>[^"]*)" почтовом smtp сервере с "(?P<user>[^"]*)" и "(?P<password>[^"]*)"$/
     */
    public function iSignInToSmtpServer($mailServer, $user, $password)
    {
        $smtpAccount = new Account($mailServer, ['login' => $user, 'password' => $password]);
        $this->getMailAgent()->connectSmtpServer($smtpAccount);
    }

    /**
     * @When /^(?:|I )sign out from mail server$/
     * @When /^(?:|я )выхожу с почтового сервера$/
     */
    public function iSignOutFromMailServer()
    {
//        $this->getMailAgent()->disconnect();
    }

    /**
     * @When /^(?:|I )clean mailbox$/
     * @When /^(?:|я )очи(щаю|стил) почтовый ящик$/
     * @When /^(?:|я )сбросил почтовый сервер$/
     */
    public function iDeleteMailMessages()
    {
        $this->getMailAgent()->removeMessages();
    }

    /**
     * @When /^(?:|I )reply with "(?P<text>(?:[^"]|\\")*)"$/
     * @When /^(?:|я )отправляю ответ с текстом "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function iReplyWithMessage($text)
    {
        $replyMail = $this->getMailAgent()->createReplyMessage($this->getMail()->getRawMail(), $text);
        $this->getMailAgent()->send($replyMail);
    }

    /**
     * @When /^(?:|I )send mail with subject "(?P<subject>(?:[^"]|\\")*)" and body "(?P<body>(?:[^"]|\\")*)" to address "(?P<to>(?:[^"]|\\")*)" from "(?P<from>(?:[^"]|\\")*)"$/
     * @When /^(?:|я )отправляю письмо с темой "(?P<subject>(?:[^"]|\\")*)" и текстом "(?P<body>(?:[^"]|\\")*)" по адресу "(?P<to>(?:[^"]|\\")*)" от "(?P<from>(?:[^"]|\\")*)"$/
     */
    public function iSendMail($subject, $body, $to, $from)
    {
        $mail = $this->getMailAgent()->createMessage($subject, $body, $from, $to);
        $this->getMailAgent()->send($mail);
    }

    /**
     * @When /^(?:|I )go to "(?P<subject>(?:[^"]|\\")*)" mail message$/
     * @When /^(?:|я )открываю письмо "(?P<subject>(?:[^"]|\\")*)"$/
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
     * @When /^(?:|я )открываю письмо, адресованное "(?P<address>(?:[^"]|\\")*)"$/
     */
    public function iGoToMailMessageWithInRecipients($address)
    {
        $this->mail = $this->getMailAgent()->getMailbox()
            ->findByRecipient($address)
            // TODO Split message to short (default exception message) and detail description.
            ->orThrow(new \Exception('Mail with "$address" in recipient addresses not found'."\nMessages:\n" . $this->getMailAgent()->getMailbox()->getMailFromToSubject()));
    }

    /**
     * @When /^(?:|я )за(шел|хожу) на почтовый сервер$/
     */
    public function iReceiveMailMessages()
    {
        $this->getMailAgent()->receive();
    }
}
