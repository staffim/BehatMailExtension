<?php

namespace Staffim\Behat\MailExtension;

class Mailbox
{
    /**
     * @var \Colada\IteratorCollection
     */
    private $mails;

    public function __construct($mails)
    {
        $this->mails = to_collection($mails)->mapBy(function($mail) {
            return new Message($mail);
        });
    }

    /**
     * @return \Colada\IteratorCollection
     */
    public function getMessages()
    {
        return $this->mails;
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
    public function getMailFromToSubject()
    {
        if ($this->getMessages()->isEmpty()) {
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
            return $mail->isEqualBySubject($subject);
        });
    }
}
