<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\TestApp\Security;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface, GitUserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @param string[] $roles
     */
    public function __construct(
        private readonly string $username,
        private readonly string $gitUserName,
        private readonly string $gitUserEmail,
        private readonly array $roles = []
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getGitUserName(): string
    {
        return $this->gitUserName;
    }

    /**
     * {@inheritdoc}
     */
    public function getGitUserEmail(): string
    {
        return $this->gitUserEmail;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
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
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        /* Noop */
    }
}
