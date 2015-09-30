<?php

namespace Staffim\Behat\MailExtension;

use ArrayObject;
use ezcMail;
use ezcMailAddress;
use ezcMailComposer;
use ezcMailFile;
use ezcMailFileSet;
use ezcMailMultipartMixed;
use ezcMailParser;
use ezcMailPop3Transport;
use ezcMailPop3TransportOptions;
use ezcMailSmtpTransport;
use ezcMailSmtpTransportOptions;
use ezcMailText;
use ezcMailTools;
use ezcMailTransportException;
use InvalidArgumentException;

use function iter\rewindable\map;
use function iter\rewindable\filter;
use function Staffim\Behat\MailExtension\X\message;

class MailAgent implements MailAgentInterface
{
    /**
     * @var ezcMailPop3Transport
     */
    private $pop3Transport;

    /**
     * @var ezcMailSmtpTransport
     */
    private $smtpTransport;

    /**
     * @var ezcMailParser
     */
    private $mailParser;

    /**
     * @var ArrayObject
     */
    private $mailbox;

    /**
     * @var Account
     */
    private $pop3Account;

    /**
     * @var Account
     */
    private $smtpAccount;

    /**
     * @var bool
     */
    private $keepMailOnServer = true;

    /**
     * @param Account $pop3Account
     * @param Account $smtpAccount
     */
    public function __construct(Account $pop3Account, Account $smtpAccount = null)
    {
        $this->pop3Account = $pop3Account;
        $this->smtpAccount = $smtpAccount;

        $this->mailParser = new ezcMailParser();

        $this->mailbox = new ArrayObject();
    }

    public function removeMailAfterReceiving()
    {
        $this->keepMailOnServer = false;
    }

    public function keepMailOnServer()
    {
        $this->keepMailOnServer = true;
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function disconnect()
    {
        $this->disconnectPop3();
        $this->disconnectSmtp();
    }

    /**
     * @return mixed
     */
    private function countMail()
    {
        $this->connectPop3Server();

        $this->pop3Transport->status($mailNumber, $dummy);

        return $mailNumber;
    }

    private function getMessages()
    {
        return map(function ($mail) { return new Message($mail); }, $this->mailbox);
    }

    /**
     * @return Mailbox
     */
    public function getMailbox()
    {
        return new Mailbox($this, $this->getMessages());
    }

    /**
     * @param string $address
     *
     * @return Mailbox
     */
    public function getMailboxFor($address)
    {
        return new Mailbox($this, filter(message()->isFor($address), $this->getMessages()));
    }

    /**
     * Retrieve new messages to mailbox
     */
    public function retrieve()
    {
        $this->connectPop3Server();

        $mails = $this->pop3Transport->fetchAll(!$this->keepMailOnServer);
        $mails = $this->mailParser->parseMail($mails);

        $this->addToMailbox($mails);
    }

    private function addToMailbox($mails)
    {
        foreach ($mails as $mail) {
            if (!$this->isMailAlreadyCopied($mail)) {
                $this->mailbox->append($mail);
            }
        }

        return $this;
    }

    private function isMailAlreadyCopied(ezcMail $newMail)
    {
        $messageId = $newMail->messageId;
        if (null === $messageId) {
            throw new InvalidArgumentException("Mail message doesn't have identifier.");
        }

        /** @var ezcMail $mail */
        foreach ($this->mailbox as $mail) {
            if ($mail->messageId === $newMail->messageId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ezcMail $mail
     */
    public function send($mail)
    {
        // TODO Check for Google's SMTP and alarm if address mismatch?..
        // TODO Check for @example.com if using real SMTP? Google, for example...

        $this->connectSmtpServer();
        $mail->subjectCharset = 'utf-8';
        $this->smtpTransport->send($mail);
    }

    /**
     * Remove messages only from server
     */
    public function removeMailFromServer()
    {
        $this->connectPop3Server();

        $count = $this->countMail();
        for ($numMessage = 1; $numMessage <= $count; $numMessage++) {
            $this->pop3Transport->delete($numMessage);
        }
    }

    /**
     * @param string $subject
     * @param string $body
     * @param string $from
     * @param string $to
     * @param array $attaches
     *
     * @return ezcMail
     */
    public function createMessage($subject, $body, $from, $to, $attaches = [])
    {
        $mail = new ezcMailComposer();
        $mail->from = new ezcMailAddress($from);
        $mail->addTo(new ezcMailAddress($to));
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
     * @param ezcMail $mail
     * @param string $text
     * @param string $fileName
     *
     * @return ezcMail
     */
    public function createReplyMessage($mail, $text, $fileName = null)
    {
        $replyMail = ezcMailTools::replyToMail($mail, $mail->to[0]);
        $textPart = new ezcMailText($text, 'utf8', '8bit', 'utf8');

        if ($fileName) {
            $fileAttachment = new ezcMailFile($fileName);
            // Specify the body of the mail as a multipart-mixed of the text part and the file attachment
            $replyMail->body = new ezcMailMultipartMixed($textPart, $fileAttachment);
        } else {
            $replyMail->body = $textPart;
        }

        return $replyMail;
    }

    /**
     * @param ezcMail $mail
     * @param string $filename
     *
     * @return ezcMail
     */
    public function createReplyMessageFromFile($mail, $filename)
    {
        $replyMail = ezcMailTools::replyToMail($mail, $mail->to[0]);
        $fileMail = $this->createMessageFromFile($filename);
        $replyMail->body = $fileMail->body;

        return $replyMail;
    }

    /**
     * Parse mail directly from files on disk
     *
     * @param string $filename
     *
     * @return ezcMail
     */
    public function createMessageFromFile($filename)
    {
        $set = new ezcMailFileSet([$filename]);
        $mail = $this->mailParser->parseMail($set);

        return $mail[0];
    }

    public function setSmtpAccount(Account $account)
    {
        $this->disconnectSmtp();

        $this->smtpAccount = $account;
    }

    private function connectSmtpServer()
    {
        $this->disconnectSmtp();

        $this->smtpTransport = new ezcMailSmtpTransport(
            $this->smtpAccount->getServerName(),
            $this->smtpAccount->getLogin(),
            $this->smtpAccount->getPassword(),
            $this->smtpAccount->getPort(),
            $this->smtpAccount->isSecure() ? new ezcMailSmtpTransportOptions(['ssl' => true]) : []
        );
    }

    private function connectPop3Server()
    {
        $this->disconnectPop3();

        $this->pop3Transport = new ezcMailPop3Transport(
            $this->pop3Account->getServerName(),
            $this->pop3Account->getPort(),
            $this->pop3Account->isSecure() ? new ezcMailPop3TransportOptions(['ssl' => true]) : []
        );
        $this->pop3Transport->authenticate($this->pop3Account->getLogin(), $this->pop3Account->getPassword());
    }

    private function disconnectSmtp()
    {
        try {
            if ($this->smtpTransport) {
                $this->smtpTransport->disconnect();
            }
        } catch (ezcMailTransportException $e) {
            // Ignore transport exceptions.
        }
    }

    private function disconnectPop3()
    {
        try {
            if ($this->pop3Transport) {
                $this->pop3Transport->disconnect();
            }
        } catch (ezcMailTransportException $e) {
            // Ignore transport exceptions.
        }
    }
}
