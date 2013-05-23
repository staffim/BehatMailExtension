<?php

namespace Staffim\Behat\MailExtension;

class Message
{
    /**
     *  @var \ezcMail
     */
    private $mail;

    public function __construct($mail)
    {
        $this->mail = $mail;
    }

    /**
     * @param string $mailServer
     * @param string $subject
     * @param string $body
     * @param string $from
     * @param string $to
     */
    // TODO Remove $mailServer from this template method. May be move to Mailbox class.
    public static function createAndSendTo($mailServer, $subject, $body, $from, $to)
    {
        $mail = new \ezcMailComposer();
        $mail->from = new \ezcMailAddress($from);
        $mail->addTo(new \ezcMailAddress($to));
        $mail->headers->offsetSet('Reply-To', $from);
        $mail->subject = stripcslashes($subject);
        $mail->plainText = stripcslashes($body);
        $mail->build();

        $transport = new \ezcMailSmtpTransport($mailServer);
        $transport->send($mail);
        $transport->disconnect();
    }

    /**
     * @param string $mailServer
     * @param string $text
     */
    // TODO Remove $mailServer from this template method. May be move to Mailbox class.
    public function reply($mailServer, $text)
    {
        $replyMail = \ezcMailTools::replyToMail($this->mail, $this->mail->to[0]);
        $replyMail->body = new \ezcMailText($text, 'utf8', '8bit', 'utf8');

        $transport = new \ezcMailSmtpTransport($mailServer);
        $transport->send($replyMail);
        $transport->disconnect();
    }

    /**
     * Not for body object.
     *
     * @param string $attr
     * @param string $text
     *
     * @return bool
     */
    private function isContainsInMailAttr($attr, $text)
    {
        return strpos($this->mail->{$attr}, stripcslashes($text)) !== false;
    }

    /**
     * @param string $subject
     *
     * @return bool
     */
    // TODO Rename to "isEqualBySubjectTo".
    public function isEqualToSubject($subject)
    {
        // Only strcmp() for strings.
        return !strcmp($this->mail->subject, $subject);
    }

    /**
     * @param string $address
     *
     * @return bool
     */
    public function isEqualToSenderAddress($address)
    {
        return $this->mail->from->email == $address;
    }

    /**
     * @param string $senderName
     *
     * @return bool
     */
    public function isEqualToSenderName($senderName)
    {
        return $this->mail->from->name == $senderName;
    }

    /**
     * @param string $address
     *
     * @return bool
     */
    public function findInTo($address)
    {
        foreach ($this->mail->to as $recipient) {
            if (strpos($recipient, $address) !== false ) {
                return true;
            };
        }
        return false;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->mail->generateBody();
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->mail->from;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->mail->to;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->mail->subject;
    }

    /**
     * @return string
     */
    public function serializeAddressHeaders()
    {
        return 'From ' . $this->mail->from . ' to ' . $this->mail->to[0] . ' with subject ' . $this->mail->subject;
    }

    /**
     * @return string
     */
    public function getPlainMessage()
    {
        return explode('Content-Type: text/html', $this->mail->generate())[0];
    }

    /**
     * @param string $text
     *
     * @return bool
     */
    public function findInBody($text)
    {
        return strpos($this->mail->generateBody(), stripcslashes($text)) !== false;
    }

    /**
     * Conteins in address mail message sender.
     * @param string $text
     *
     * @return bool
     */
    public function findInFrom($text)
    {
        return $this->isContainsInMailAttr('from', $text);
    }

    /**
     * string @param $text
     *
     * @return bool
     */
    public function findInSubject($text)
    {
        return $this->isContainsInMailAttr('subject', $text);
    }

    /**
     * Conteins in (from, subject, body) mail attributes.
     * @param string $text
     *
     * @return bool
     */
    public function isContains($text)
    {
        return $this->findInSubject($text) || $this->findInFrom($text) || $this->findInBody($text);
    }

    /**
     * Conteins in mail attachments filename.
     */
    public function findInAttachment($name)
    {
        foreach($this->mail->body->getParts() as $part) {
            if ($part->contentDisposition) {
                $filename = $part->contentDisposition->displayFileName;
                return strpos($filename, stripcslashes($name)) !== false;
            }
        }
    }
}
