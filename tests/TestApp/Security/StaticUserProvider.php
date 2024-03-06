<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\TestApp\Security;

use Override;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<User>
 */
class StaticUserProvider implements UserProviderInterface
{
    /** @var array<string, User> */
    private array $users;

    public function __construct()
    {
        $this->users = [];
        $this->users['user'] = new User('user', 'John Doe', 'johndoe@examle.com', ['ROLE_USER']);
        $this->users['admin'] = new User('admin', 'Mary Dane', 'marydane@examle.com', ['ROLE_USER', 'ROLE_ADMIN']);
    }

    #[Override]
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        if (!array_key_exists($identifier, $this->users)) {
            throw new UserNotFoundException();
        }

        return $this->users[$identifier];
    }

    #[Override]
    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    #[Override]
    public function supportsClass(string $class): bool
    {
        return $class === User::class;
    }
}
