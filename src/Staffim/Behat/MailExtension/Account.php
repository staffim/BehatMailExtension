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
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @param $server string server name
     * @param $auth array of ['login': login, 'password': password]
     */
    public function __construct($server, $auth=['login' => '', 'password' => ''])
    {
        $this->server = $server;
        $this->user = $auth['login'];
        $this->password = $auth['password'];
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
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
