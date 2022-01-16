<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Utils;

use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class StaticUserProvider implements UserProviderInterface
{
    /** @var array<string, User> */
    private $users;

    public function __construct()
    {
        $this->users = [];
        $this->users['user'] = new User('user', 'John Doe', 'johndoe@examle.com', ['ROLE_USER']);
        $this->users['admin'] = new User('admin', 'Mary Dane', 'marydane@examle.com', ['ROLE_USER', 'ROLE_ADMIN']);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername(string $username): UserInterface
    {
        if (!array_key_exists($username, $this->users)) {
            throw new UserNotFoundException();
        }

        return $this->users[$username];
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass(string $class): bool
    {
        return $class === User::class;
    }
}
