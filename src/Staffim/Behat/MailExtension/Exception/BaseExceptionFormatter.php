<?php

namespace Staffim\Behat\MailExtension\Exception;

use Staffim\Behat\MailExtension\Message;

class BaseExceptionFormatter implements ExceptionFormatter
{
    /**
     * Prepends every line in a string with pipe (|).
     *
     * @param string $string
     *
     * @return string
     */
    protected function pipeString($string)
    {
        return '|  ' . strtr($string, array("\n" => "\n|  "));
    }

    /**
     * Removes response header/footer, letting only <body /> content and trim it.
     *
     * @param string  $string response content
     * @param integer $count  trim count
     *
     * @return string
     */
    protected function trimBody($string, $count = 3000)
    {
        $string = preg_replace(array('/^.*<body>/s', '/<\/body>.*$/s'), array('<body>', '</body>'), $string);
        $string = $this->trimString($string, $count);

        return $string;
    }

    /**
     * Trims string to specified number of chars.
     *
     * @param string  $string response content
     * @param integer $count  trim count
     *
     * @return string
     */
    protected function trimString($string, $count = 3000)
    {
        $string = trim($string);

        if ($count < mb_strlen($string)) {
            return mb_substr($string, 0, $count - 3) . '...';
        }

        return $string;
    }

    /**
     * @param string $message
     * @param \Staffim\Behat\MailExtension\Message $mail
     *
     * @return string
     */
    function __invoke($message, Message $mail)
    {
        $mailBody = $this->trimString($mail->getRawParsedMessage());

        return sprintf("%s\n\nRaw message:\n%s",
            $message,
            $this->pipeString($mailBody."\n")
        );
    }
}
