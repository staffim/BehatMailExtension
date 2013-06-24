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

    /**
     * @var Account
     */
    private $pop3Account;

    /**
     * @param Account $pop3Account
     */
    public function setPop3Account($pop3Account)
    {
        $this->pop3Account = $pop3Account;
    }

    /**
     * @var Account
     */
    private $smtpAccount;

    /**
     * @param Account $smtpAccount
     */
    public function setSmtpAccount($smtpAccount)
    {
        $this->smtpAccount = $smtpAccount;
    }

    /**
     * @param AccountInterface $pop3Account
     * @param AccountInterface $smtpAccount
     */
    public function __construct(AccountInterface $pop3Account, AccountInterface $smtpAccount = null)
    {
        $this->pop3Account = $pop3Account;
        $this->smtpAccount = $smtpAccount;

        $this->pop3Transport = new \ezcMailPop3Transport($pop3Account->getServerName());
        $this->mailParser = new \ezcMailParser();
    }

    /**
     * @param AccountInterface $smtpAccount
     */
    public function connectSmtpServer(AccountInterface $smtpAccount = null)
    {
        $this->disconnectSmtp();

        $smtpAccount = $smtpAccount ? $smtpAccount: $this->smtpAccount;
        $this->smtpTransport = new \ezcMailSmtpTransport($smtpAccount->getServerName(), $smtpAccount->getLogin(), $smtpAccount->getPassword());
    }

    /**
     * @param AccountInterface $pop3Account
     */
    public function connectPop3Server(AccountInterface $pop3Account = null)
    {
        $this->disconnectPop3();

        $pop3Account = $pop3Account ? $pop3Account: $this->pop3Account;
        $this->pop3Transport = new \ezcMailPop3Transport($pop3Account->getServerName());
        $this->pop3Transport->authenticate($pop3Account->getLogin(), $pop3Account->getPassword());
    }

    public function disconnectSmtp()
    {
        try {
            if ($this->smtpTransport) {
                $this->smtpTransport->disconnect();
            }
        } catch (\ezcMailTransportException $e) {
            // Ignore transport exceptions.
        }
    }

    public function disconnectPop3()
    {
        try {
            if ($this->pop3Transport) {
                $this->pop3Transport->disconnect();
            }
        } catch (\ezcMailTransportException $e) {
            // Ignore transport exceptions.
        }
    }

    public function disconnect()
    {
        $this->disconnectPop3();
        $this->disconnectSmtp();
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * @return \ezcMailSmtpTransport
     */
    public function getSmtpTransport()
    {
        if (!$this->smtpTransport) {
            $this->connectSmtpServer();
        }

        return $this->smtpTransport;
    }

    public function size()
    {
        $this->connectPop3Server();
        $this->pop3Transport->status($dummy, $sizeMail);

        return $sizeMail;
    }

    /**
     * @return mixed
     */
    public function number()
    {
        $this->connectPop3Server();
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
     */
    public function getMailbox()
    {
        if (!$this->mailbox) {
            $this->receive();
        }

        return $this->mailbox;
    }

    /**
     * Receive messages to mailbox
     *
     * @return Mailbox
     */
    public function receive()
    {
        $this->connectPop3Server();

        $mails = $this->pop3Transport->fetchAll();
        $mails = $this->mailParser->parseMail($mails);

        $mailbox = new Mailbox($mails);
        $this->setMailbox($mailbox);

        return $mailbox;
    }

    /**
     * @param \ezcMail $mail
     */
    public function send($mail)
    {
        $this->connectSmtpServer();
        $this->smtpTransport->send($mail);
    }

    /**
     * Remove messages from server
     */
    public function removeMessages()
    {
        $this->connectPop3Server();
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
     * @param array $attaches
     *
     * @return \ezcMail
     */
    public function createMessage($subject, $body, $from, $to, $attaches = [])
    {
        $mail = new \ezcMailComposer();
        $mail->from = new \ezcMailAddress($from);
        $mail->addTo(new \ezcMailAddress($to));
        $mail->headers->offsetSet('Reply-To', $from);
        $mail->subject = stripcslashes($subject);
        $mail->plainText = stripcslashes($body);

        if ($attaches) {
            foreach ($attaches as $attach) {
                $mail->addFileAttachment($attach);
            }
        }

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

    /**
     * Waits some time or number messages.
     *
     * @param integer $time      time in milliseconds
     * @param integer $number
     *
     * @return bool
     */
    public function wait($time, $number = 0)
    {
        $start = microtime(true);
        $end = $start + $time / 1000.0;

        do {
            $result = (bool) ($number <= $this->number());
            usleep(100000);
        } while ( microtime(true) < $end && !$result );

        return (bool)$result;
    }
}
