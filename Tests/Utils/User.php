<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Utils;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class User implements UserInterface, GitUserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $gitUserName;

    /**
     * @var string
     */
    private $gitUserEmail;

    /**
     * @var string[]
     */
    private $roles;

    public function __construct(string $username, string $gitUserName, string $gitUserEmail, array $roles = [])
    {
        $this->username = $username;
        $this->gitUserName = $gitUserName;
        $this->gitUserEmail = $gitUserEmail;
        $this->roles = $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getGitUserName()
    {
        return $this->gitUserName;
    }

    /**
     * {@inheritdoc}
     */
    public function getGitUserEmail()
    {
        return $this->gitUserEmail;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword(): string
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        /* Noop */
    }
}
