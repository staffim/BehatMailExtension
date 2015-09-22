<?php

namespace Staffim\Behat\MailExtension;

class Account
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
     * @var bool
     */
    private $secure;

    /**
     * @param string $server
     * @param int $port
     * @param string $login
     * @param string $password
     * @param bool $secure
     */
    public function __construct($server, $port, $login = '', $password = '', $secure = true)
    {
        $this->server = $server;
        $this->port = $port;
        $this->login = $login;
        $this->password = $password;
        $this->secure = $secure;
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

    /**
     * @return bool
     */
    public function isSecure()
    {
        return $this->secure;
    }
}
