<?php

namespace Staffim\Behat\MailExtension\Exception;

class MessageBodyException extends MessageException
{
    /**
     * Returns exception message with additional context info.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            $mailBody = $this->trimString($this->getMailMessage()->getBody());
            $string = sprintf("%s\n\nMail body:\n%s",
                $this->getMessage(),
                $this->pipeString($mailBody."\n")
            );
        } catch (\Exception $e) {
            return $this->getMessage();
        }

        return $string;
    }
}
