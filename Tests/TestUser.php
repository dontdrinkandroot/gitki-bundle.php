<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Tests;

use Dontdrinkandroot\Gitki\BaseBundle\Model\GitUserInterface;

class TestUser implements GitUserInterface
{

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $email;

    /**
     * @param string $username
     * @param string $email
     */
    public function __construct($username, $email)
    {

        $this->username = $username;
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    public function getRoles()
    {
    }

    public function getPassword()
    {
    }

    public function getSalt()
    {
    }

    public function eraseCredentials()
    {
    }
}