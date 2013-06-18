<?php

namespace Staffim\Behat\MailExtension;

class Account implements AccountInterface
{
    /**
     * @var string
     */
    private $server;

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $password;

    /**
     * @param $server string server name
     * @param string $login
     * @param string $password
     */
    public function __construct($server, $login = '', $password = '')
    {
        $this->server = $server;
        $this->login = $login;
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getServerName()
    {
        return $this->server;
    }
}
