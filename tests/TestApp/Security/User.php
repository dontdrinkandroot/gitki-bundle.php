<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\TestApp\Security;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Override;
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

    #[Override]
    public function getGitUserName(): string
    {
        return $this->gitUserName;
    }

    #[Override]
    public function getGitUserEmail(): string
    {
        return $this->gitUserEmail;
    }

    #[Override]
    public function getRoles(): array
    {
        return $this->roles;
    }

    #[Override]
    public function getPassword(): string
    {
        return $this->username;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    #[Override]
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    #[Override]
    public function eraseCredentials(): void
    {
        /* Noop */
    }
}
