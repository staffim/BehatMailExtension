<?php

namespace Staffim\Behat\MailExtension;

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

        $this->mailParser = new \ezcMailParser();
    }

    /**
     * @param AccountInterface $smtpAccount
     */
    public function connectSmtpServer(AccountInterface $smtpAccount = null)
    {
        $this->disconnectSmtp();

        $smtpAccount = $smtpAccount ? $smtpAccount: $this->smtpAccount;
        $this->smtpTransport = new \ezcMailSmtpTransport(
            $smtpAccount->getServerName(),
            $smtpAccount->getLogin(),
            $smtpAccount->getPassword(),
            $smtpAccount->getPort()
        );
    }

    // TODO Refactor to getPop3Transport().
    private function connectPop3Server()
    {
        $this->disconnectPop3();

        $this->pop3Transport = new \ezcMailPop3Transport($this->pop3Account->getServerName(), $this->pop3Account->getPort());
        $this->pop3Transport->authenticate($this->pop3Account->getLogin(), $this->pop3Account->getPassword());
    }

    private function disconnectSmtp()
    {
        try {
            if ($this->smtpTransport) {
                $this->smtpTransport->disconnect();
            }
        } catch (\ezcMailTransportException $e) {
            // Ignore transport exceptions.
        }
    }

    private function disconnectPop3()
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
    public function setMailbox(Mailbox $mailbox)
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
        $mail->subjectCharset = 'utf-8';
        $this->smtpTransport->send($mail);
    }

    /**
     * Remove messages only from server
     */
    public function RemoveMessagesFromServer()
    {
        $this->connectPop3Server();

        $count = $this->number();
        for ($numMessage = 1; $numMessage <= $count; $numMessage++) {
            $this->pop3Transport->delete($numMessage);
        }

        $this->pop3Transport->disconnect();
    }

    /**
     * Remove messages from server and from mailbox
     */
    public function removeMessages()
    {
        $this->RemoveMessagesFromServer();
        $this->mailbox = null;
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
     * @param string $fileName
     *
     * @return \ezcMail
     */
    public function createReplyMessage($mail, $text, $fileName = null)
    {
        $replyMail = \ezcMailTools::replyToMail($mail, $mail->to[0]);
        $textPart = new \ezcMailText($text, 'utf8', '8bit', 'utf8');

        if ($fileName) {
            $fileAttachment = new \ezcMailFile($fileName);
            // Specify the body of the mail as a multipart-mixed of the text part and the file attachment
            $replyMail->body = new \ezcMailMultipartMixed($textPart, $fileAttachment);
        } else {
            $replyMail->body = $textPart;
        }

        return $replyMail;
    }

    /**
     * @param \ezcMail $mail
     * @param string $filename
     *
     * @return \ezcMail
     */
    public function createReplyMessageFromFile($mail, $filename)
    {
        $replyMail = \ezcMailTools::replyToMail($mail, $mail->to[0]);
        $fileMail = $this->createMessageFromFile($filename);
        $replyMail->body = $fileMail->body;

        return $replyMail;
    }

    /**
     * Parse mail directly from files on disk
     *
     * @param string $filename
     *
     * @return \ezcMail
     */
    public function createMessageFromFile($filename)
    {
        $set = new \ezcMailFileSet([$filename]);
        $mail = $this->mailParser->parseMail($set);

        return $mail[0];
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
        } while (microtime(true) < $end && !$result);

        return (bool)$result;
    }
}
