<?php

namespace Staffim\Behat\MailExtension;

interface AccountInterface
{
    /**
     * @return string
     */
    public function getLogin();

    /**
     * @return string
     */
    public function getPassword();

    /**
     * @return string
     */
    public function getServerName();
}
