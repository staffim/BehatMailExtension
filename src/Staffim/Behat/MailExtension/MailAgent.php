<?php

namespace Staffim\Behat\MailExtension;

use Staffim\Behat\MailExtension\Mailbox;

class MailAgent implements MailAgentInterface
{
    /**
     * @var \ezcMailPop3Transport
     */
    private $pop3Transport;

    /**
     * @var \ezcMailSmtpTransport
     */
    private $smtpTransport;

    /**
     * @var \ezcMailParser
     */
    private $mailParser;

    /**
     * @var Mailbox
     */
    private $mailbox;

    public function __construct(AccountInterface $pop3Account, AccountInterface $smtpAccount=null)
    {
        $this->pop3Transport = new \ezcMailPop3Transport($pop3Account->getServerName());
        $this->pop3Transport->authenticate($pop3Account->getUser(), $pop3Account->getPassword());

        $this->mailParser = new \ezcMailParser();

        if ($smtpAccount) {
            $this->connectSmtpServer($smtpAccount);
        }
    }

    public function connectSmtpServer(AccountInterface $smtpAccount)
    {
        try {
            if (!!$this->smtpTransport) {
                $this->smtpTransport->disconnect();
            }
        } catch (\ezcMailTransportException $e) {
            // Ignore transport exceptions.
        }

        $this->smtpTransport = new \ezcMailSmtpTransport($smtpAccount->getServerName(), $smtpAccount->getUser(), $smtpAccount->getPassword());
    }

    public function connectPop3Server(AccountInterface $pop3Account)
    {
        try {
            if (!!$this->pop3Transport) {
                $this->pop3Transport->disconnect();
            }
        } catch (\ezcMailTransportException $e) {
            // Ignore transport exceptions.
        }

        $this->pop3Transport = new \ezcMailPop3Transport($pop3Account->getServerName());
        $this->pop3Transport->authenticate($pop3Account->getUser(), $pop3Account->getPassword());
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function disconnect()
    {
        try {
            $this->pop3Transport->disconnect();
            $this->smtpTransport->disconnect();
        } catch (\ezcMailTransportException $e) {
             // Ignore transport exceptions.
        }
    }

    public function getSmtpTransport()
    {
        if (!!$this->smtpTransport) {

            return $this->smtpTransport;
        }
    }


    public function size()
    {
        $this->pop3Transport->status($dummy, $sizeMail);

        return $sizeMail;
    }

    public function number()
    {
        $this->pop3Transport->status($numMail, $dummy);

        return $numMail;
    }

    /**
     * @param Mailbox $mailbox
     */
    public function setMailbox($mailbox)
    {
        $this->mailbox = $mailbox;
    }

    /**
     * @return Mailbox
     *
     * @throws \Exception
     */
    // TODO To option.
    public function getMailbox()
    {
        if (!$this->mailbox) {
            $this->receive();
        }

        return $this->mailbox;
    }

    /**
     * Receive messages to mailbox
     */
    public function receive()
    {
        var_dump("Receive", $this->number());
        $mails = $this->pop3Transport->fetchAll();
        $mails = $this->mailParser->parseMail($mails);

        $this->setMailbox(new Mailbox($mails));
    }

    /**
     * @param \ezcMail $mail
     */
    public function send($mail)
    {
        $this->smtpTransport->send($mail);
    }

    /**
     * Remove messages from server
     */
    public function removeMessages()
    {
        $count = $this->number();
        for ($numMessage = 1; $numMessage <= $count; $numMessage++) {
            $this->pop3Transport->delete($numMessage);
        }

        $this->mailbox = null;
        $this->pop3Transport->disconnect();
    }

    /**
     * @param string $subject
     * @param string $body
     * @param string $from
     * @param string $to
     *
     * @return \ezcMail
     */
    public function createMessage($subject, $body, $from, $to)
    {
        $mail = new \ezcMailComposer();
        $mail->from = new \ezcMailAddress($from);
        $mail->addTo(new \ezcMailAddress($to));
        $mail->headers->offsetSet('Reply-To', $from);
        $mail->subject = stripcslashes($subject);
        $mail->plainText = stripcslashes($body);
        $mail->build();

        return $mail;
    }

    /**
     * @param \ezcMail $mail
     * @param string $text
     *
     * @return \ezcMail
     */
    public function createReplyMessage($mail, $text)
    {
        $replyMail = \ezcMailTools::replyToMail($mail, $mail->to[0]);
        $replyMail->body = new \ezcMailText($text, 'utf8', '8bit', 'utf8');

        return $replyMail;
    }

    public function addAttach($mail, $filepath)
    {
        return $mail;
    }
}
