<?php

namespace Staffim\Behat\MailExtension;

class Mailbox
{
    /**
     * @var \ezcMailPop3Transport
     */
    private $mailTransport;

    /**
     * @var int
     */
    private $numMail;

    /**
     * @var \Colada\IteratorCollection
     */
    private $mails;

    /**
     * @var int
     */
    private $sizeMail;

    public function __construct($mailServer, $user, $password)
    {
        $this->mailTransport = new \ezcMailPop3Transport($mailServer);

        $mailParser = new \ezcMailParser();

        $this->mailTransport->authenticate($user, $password);

        $mails = $this->mailTransport->fetchAll();
        $mails = $mailParser->parseMail($mails);

        $this->mails = to_collection($mails)->mapBy(function($mail) {
            return new Message($mail);
        });
    }

    /**
     * Authenticate on mail server and delete all messages.
     *
     * @deprecated
     *
     * @param string $mailServer
     * @param string $user
     * @param string $password
     *
     * @return bool
     */
    // TODO Remove this method — connect and remove all message from connected object instead.
    public static function resetMailbox($mailServer, $user, $password)
    {
        $mailTransport = new \ezcMailPop3Transport($mailServer);
        $mailTransport->authenticate($user, $password);

        $count = 0;
        $size  = 0;
        $mailTransport->status($count, $size);

        for ($numMessage = 1; $numMessage <= $count; $numMessage++) {
            $mailTransport->delete($numMessage);
        }

        $mailTransport->disconnect();
    }

    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * @return \Colada\IteratorCollection
     */
    // TODO Rename to "getMessages".
    public function getMails()
    {
        return $this->mails;
    }

    public function disconnect()
    {
        try {
            $this->mailTransport->disconnect();
        } catch (\ezcMailTransportException $e) {
             // Ignore transport exceptions.
        }
    }


    public function deleteMail()
    {
        $count = $this->number();
        for ( $numMessage = 1; $numMessage <= $count; $numMessage++) {
            $this->mailTransport->delete($numMessage);
        }
    }

    public function size()
    {
        $this->mailTransport->status($this->numMail, $this->sizeMail);

        return $this->sizeMail;
    }

    public function number()
    {
        $this->mailTransport->status($this->numMail, $this->sizeMail);

        return $this->numMail;
    }

    /**
     * Find mail which contain $text.
     *
     * @param string $text
     *
     * @return \Colada\Collection
     */
    public function getMailByText($text)
    {
        return $this->mails->acceptBy(function(Message $mail) use($text) {
            return $mail->isContains($text);
        });
    }

    /**
     * @return string
     */
    // TODO WTF?! Rename to more informative name.
    public function getMailInfo()
    {
        if ($this->getMails()->isEmpty()) {
            return 'Mailbox is empty';
        }

        return $this->mails->mapBy(function(Message $mail) {
            return $mail->serializeAddressHeaders();
        })->join("\n");
    }

    /**
     * @param string $subject
     *
     * @return \Colada\Collection
     */
    public function findBySubject($subject)
    {
        return $this->mails->findBy(function(Message $mail) use($subject) {
            return $mail->findInSubject($subject);
        });
    }

    /**
     * @param $text
     *
     * @return \Colada\Collection
     */
    public function findByText($text)
    {
        return $this->mails->findBy(function(Message $mail) use($text) {
            return $mail->findInBody($text);
        });
    }

    /**
     * @param $sender
     *
     * @return \Colada\Collection
     */
    public function findBySender($sender)
    {
        return $this->mails->findBy(function(Message $mail) use($sender) {
            return $mail->findInFrom($sender);
        });
    }

    /**
     * @param $address
     *
     * @return \Colada\Collection
     */
    public function findByRecipient($address)
    {
        return $this->mails->findBy(function(Message $mail) use($address) {
            return $mail->findInTo($address);
        });
    }

    /**
     * @param string $subject
     *
     * @return \Colada\Collection
     */
    public function findByEqualSubject($subject)
    {
        return $this->mails->findBy(function(Message $mail) use($subject) {
            return $mail->isEqualToSubject($subject);
        });
    }
}
