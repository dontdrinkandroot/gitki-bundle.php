<?php

namespace Dontdrinkandroot\GitkiBundle\Tests;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class TestUser implements GitUserInterface, UserInterface
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
    public function getGitUserName()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getGitUserEmail()
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

    public function getUsername()
    {
        return $this->username;
    }
}
