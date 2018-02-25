<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Security;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\GitkiBundle\Service\Role\RoleServiceInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class SecurityService
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var RoleServiceInterface
     */
    private $roleService;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        RoleServiceInterface $roleService
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->roleService = $roleService;
        $this->authorizationChecker = $authorizationChecker;
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

    /**
     * @return GitUserInterface|null
     */
    public function findGitUser()
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
     * @throws \Exception Thrown if the current user is not set or not a GitUserInterface.
     */
    public function getGitUser()
    {
        $user = $this->findGitUser();
        if (null === $user) {
            throw new \Exception('No user was found');
        }

        return $user;
    }

    public function assertWatcher()
    {
        if (!$this->authorizationChecker->isGranted($this->roleService->getWatcherRole())) {
            throw new AuthenticationException();
        }
    }

    public function isCommitter(): bool
    {
        return $this->authorizationChecker->isGranted($this->roleService->getCommitterRole());
    }

    public function assertCommitter()
    {
        if (!$this->isCommitter()) {
            throw new AuthenticationException();
        }
    }

    public function assertAdmin()
    {
        if (!$this->authorizationChecker->isGranted($this->roleService->getAdminRole())) {
            throw new AuthenticationException();
        }
    }
}
