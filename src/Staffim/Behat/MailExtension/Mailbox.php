<?php

namespace Staffim\Behat\MailExtension;

use Iterator;
use IteratorAggregate;
use PhpOption\Option;

use function Functional\select;
use function Functional\map;
use function Functional\first;
use function Functional\last;
use function Colada\x;

class Mailbox implements IteratorAggregate
{
    /**
     * @var Iterator
     */
    private $mails;

    /**
     * @var MailAgent
     */
    private $agent;

    public function __construct(MailAgent $agent, $mails)
    {
        $this->agent = $agent;
        $this->mails = $mails;
    }

    public function getIterator()
    {
        return $this->mails;
    }

    /**
     * @deprecated Use as iterator instead.
     *
     * @return Iterator
     */
    public function getMessages()
    {
        return $this->getIterator();
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return iterator_count($this->mails);
    }

    public function isEmpty()
    {
        return $this->getSize() === 0;
    }

    /**
     * @param callable $checker
     * @param int $time Time in milliseconds.
     *
     * @return bool
     */
    public function waitFor($checker, $time)
    {
        $start = microtime(true);
        $end = $start + $time / 1000.0;

        do {
            $this->agent->retrieve();

            $result = $checker($this);
            // 0.5 second.
            usleep(500000);
        } while ((microtime(true) < $end) && !$result);

        return $result;
    }

    /**
     * @param int $size
     * @param int $time Time in milliseconds.
     *
     * @return bool
     */
    public function waitForSize($size, $time)
    {
        return $this->waitFor(function ($mailbox) use ($size) {
            return $size <= $mailbox->getSize();
        }, $time);
    }

    /**
     * Find mail which contain $text.
     *
     * @param string $text
     *
     * @return array
     */
    public function getMailByText($text)
    {
        return select($this->mails, x()->isContains($text));
    }

    /**
     * Useful for debug.
     *
     * @return string
     */
    public function getMailFromToSubject()
    {
        if ($this->isEmpty()) {
            return 'Mailbox is empty';
        }

        return implode("\n", map($this->mails, x()->serializeAddressHeaders()));
    }

    /**
     * @param string $subject
     *
     * @return array
     */
    public function findBySubject($subject)
    {
        return select($this->mails, x()->findInSubject($subject));
    }

    /**
     * @param string $subject
     *
     * @return Option
     */
    public function lastBySubject($subject)
    {
        return Option::fromValue(last($this->findBySubject($subject)));
    }

    /**
     * @param $text
     *
     * @return array
     */
    public function findByText($text)
    {
        return select($this->mails, x()->findInBody($text));
    }

    /**
     * @param $sender
     *
     * @return array
     */
    public function findBySender($sender)
    {
        return select($this->mails, x()->findInFrom($sender));
    }

    /**
     * @param $address
     *
     * @return array
     */
    public function findByRecipient($address)
    {
        return select($this->mails, x()->findInTo($address));
    }

    /**
     * @param string $address
     *
     * @return Option
     */
    public function lastByRecipient($address)
    {
        return Option::fromValue(last($this->findByRecipient($address)));
    }

    /**
     * @param string $subject
     *
     * @return array
     */
    public function findByEqualSubject($subject)
    {
        return select($this->mails, x()->isEqualBySubject($subject));
    }
}
