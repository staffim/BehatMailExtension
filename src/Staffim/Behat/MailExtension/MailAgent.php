<?php

namespace Staffim\Behat\MailExtension;

use Message;

class MailAgent
//    implements MailAgentInterface
{
    /**
     * @var \ezcMailPop3Transport
     */
    private $mailPop3Transport;

    /**
     * @var \ezcMailParser
     */
    private $mailParser;

    /**
     * @var String
     */
    private $mailServer;

    /**
     * @var Mailbox
     */
    private $mailbox;

    public function __construct($mailPop3Server, $user, $password)
    {
        $this->mailServer = $mailPop3Server;
        $this->mailPop3Transport = new \ezcMailPop3Transport($mailPop3Server);
        $this->mailParser = new \ezcMailParser();

        $this->mailPop3Transport->authenticate($user, $password);
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    public function disconnect()
    {
        try {
            $this->mailPop3Transport->disconnect();
        } catch (\ezcMailTransportException $e) {
             // Ignore transport exceptions.
        }
    }

    public function deleteMessages()
    {
        $count = $this->number();
        for ( $numMessage = 1; $numMessage <= $count; $numMessage++) {
            $this->mailPop3Transport->delete($numMessage);
        }
    }

    public function size()
    {
        $this->mailPop3Transport->status($dummy, $sizeMail);

        return $sizeMail;
    }

    public function number()
    {
        $this->mailPop3Transport->status($numMail, $dummy);

        return $numMail;
    }

    public function receiveMail()
    {
        $mails = $this->mailPop3Transport->fetchAll();
        $mails = $this->mailParser->parseMail($mails);

        $this->setMailbox(new Mailbox($mails));
    }

    /**
     * @return \Staffim\Behat\MailExtension\Mailbox
     *
     * @throws \Exception
     */
    // TODO To option.
    public function getMailbox()
    {
        if (!$this->mailbox) {
            throw new \Exception('Mailbox not created');
        }

        return $this->mailbox;
    }

    /**
     * @param \Staffim\Behat\MailExtension\Mailbox $mailbox
     */
    public function setMailbox($mailbox)
    {

        $this->mailbox = $mailbox;
    }

    /**
     * @param string $subject
     * @param string $body
     * @param string $from
     * @param string $to
     */
    public function createAndSendTo($subject, $body, $from, $to)
    {
        $mail = new \ezcMailComposer();
        $mail->from = new \ezcMailAddress($from);
        $mail->addTo(new \ezcMailAddress($to));
        $mail->headers->offsetSet('Reply-To', $from);
        $mail->subject = stripcslashes($subject);
        $mail->plainText = stripcslashes($body);
        $mail->build();

        $transport = new \ezcMailSmtpTransport($this->mailServer);
        $transport->send($mail);
        $transport->disconnect();
    }

    /**
     * @param string $text
     */
    public function reply($text)
    {
        $replyMail = \ezcMailTools::replyToMail($this->mail, $this->mail->to[0]);
        $replyMail->body = new \ezcMailText($text, 'utf8', '8bit', 'utf8');

        $transport = new \ezcMailSmtpTransport($this->mailServer);
        $transport->send($replyMail);
        $transport->disconnect();
    }
}
