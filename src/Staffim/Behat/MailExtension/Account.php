<?php

namespace Staffim\Behat\MailExtension;

class Account implements AccountInterface
{
    /**
     * @var string
     */
    private $server;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $login;

    /**
     * @var string
     */
    private $password;

    /**
     * @param string $server
     * @param int $port
     * @param string $login
     * @param string $password
     */
    public function __construct($server, $port, $login = '', $password = '')
    {
        $this->server = $server;
        $this->port = $port;
        $this->login = $login;
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getServerName()
    {
        return $this->server;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
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
}
