<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Security;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Exception;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityService
{

    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function getUser(): ?UserInterface
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        if (!is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    public function findGitUser(): ?GitUserInterface
    {
        $user = $this->getUser();
        if (null === $user) {
            return null;
        }

        if (!($user instanceof GitUserInterface)) {
            return null;
        }

        return $user;
    }

    /**
     * @return GitUserInterface
     *
     * @throws Exception Thrown if the current user is not set or not a GitUserInterface.
     */
    public function getGitUser(): GitUserInterface
    {
        $user = $this->findGitUser();
        if (null === $user) {
            throw new Exception('No user was found');
        }

        return $user;
    }
}
