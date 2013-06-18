<?php

namespace Staffim\Behat\MailExtension;

class Message
{
    /**
     *  @var \ezcMail
     */
    private $mail;

    /**
     * @return \ezcMail
     */
    public function getRawMail()
    {
        return $this->mail;
    }

    public function __construct($mail)
    {
        $this->mail = $mail;
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
    public function isEqualBySubject($subject)
    {
        // Only strcmp() for strings.
        return !strcmp($this->mail->subject, $subject);
    }

    /**
     * @param string $address
     *
     * @return bool
     */
    public function isEqualBySenderAddress($address)
    {
        return $this->mail->from->email == $address;
    }

    /**
     * @param string $senderName
     *
     * @return bool
     */
    public function isEqualBySenderName($senderName)
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
        $plainMessage = explode('Content-Type: text/html;', $this->mail->generate())[0];

        return $plainMessage;
    }

    /**
     * @return string
     */
    public function getHtmlMessage()
    {
        $htmlMessage = 'No HTML version';

        $mailParts = explode('Content-Type: text/html; charset=utf-8', $this->mail->generate());
        if (count($mailParts) > 1) {
            $htmlMessage = [1];
        }

        return $htmlMessage;
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

    /**
     * @throws \Exception If no matches found.
     *
     * @param $pattern
     *
     * @return string[]
     */
    public function findBodyMatches($pattern)
    {
        preg_match($pattern, $this->getBody(), $matches);
        if (empty($matches)) {
            // TODO Split message to short (default exception message) and detail description.
            throw new \Exception(sprintf('Not matches for pattern "%s" in message body: %s', $pattern, $this->getBody()));
        }

        return $matches;
    }
}
