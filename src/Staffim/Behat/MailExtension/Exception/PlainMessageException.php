<?php

namespace Staffim\Behat\MailExtension\Exception;

class PlainMessageException extends MessageException
{
    /**
     * Returns exception message with additional context info.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $mailBody = $this->trimString($this->getMailMessage()->getPlainMessage());
            $string = sprintf("%s\n\nPlain message:\n%s",
                $this->getMessage(),
                $this->pipeString($mailBody."\n")
            );
        } catch (\Exception $e) {
            return $this->getMessage();
        }

        return $string;
    }
}
