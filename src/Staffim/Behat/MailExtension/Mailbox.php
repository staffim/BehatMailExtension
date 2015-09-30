<?php

namespace Staffim\Behat\MailExtension;

use Iterator;
use IteratorAggregate;
use PhpOption\Option;

use function Functional\select;
use function Functional\map;
use function Functional\last;
use function Staffim\Behat\MailExtension\X\message;

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
     * @return bool Waiting result (successful or not).
     */
    public function waitFor($checker, $time)
    {
        $start = microtime(true);
        $end = $start + $time / 1000.0;

        // Check condition first, with existing mailbox state.
        $result = $checker($this);
        while (!$result && (microtime(true) < $end)) {
            // And retrieve new messages, if condition evaluated to false...
            $this->agent->retrieve();

            $result = $checker($this);
            // 0.5 second.
            usleep(500000);
        }

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
        return $this->waitFor(function (Mailbox $mailbox) use ($size) {
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
        return select($this->mails, message()->isContains($text));
    }

    /**
     * @param string $subject
     *
     * @return array
     */
    public function getBySubject($subject)
    {
        return select($this->mails, message()->findInSubject($subject));
    }

    /**
     * @param string $subject
     *
     * @return Option
     */
    public function findLastBySubject($subject)
    {
        return Option::fromValue(last($this->getBySubject($subject)));
    }

    /**
     * @param $text
     *
     * @return array
     */
    public function getByText($text)
    {
        return select($this->mails, message()->findInBody($text));
    }

    /**
     * @param $sender
     *
     * @return array
     */
    public function getBySender($sender)
    {
        return select($this->mails, message()->findInFrom($sender));
    }

    /**
     * @param $address
     *
     * @return array
     */
    public function getByRecipient($address)
    {
        return select($this->mails, message()->findInTo($address));
    }

    /**
     * @param string $address
     *
     * @return Option
     */
    public function findLastByRecipient($address)
    {
        return Option::fromValue(last($this->getByRecipient($address)));
    }

    /**
     * @param string $subject
     *
     * @return array
     */
    public function getByConcreteSubject($subject)
    {
        return select($this->mails, message()->isEqualBySubject($subject));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->isEmpty()) {
            return 'Mailbox is empty';
        }

        return implode("\n", map($this->mails, message()->__toString())) . "\n";
    }
}
