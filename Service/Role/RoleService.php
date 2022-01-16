<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Role;

/**
 * @deprecated Use Voters instead
 */
class RoleService implements RoleServiceInterface
{
    protected string $watcherRole = 'IS_AUTHENTICATED_ANONYMOUSLY';

    protected string $committerRole = 'ROLE_USER';

    protected string $adminRole = 'ROLE_ADMIN';

    /**
     * {@inheritdoc}
     */
    public function getWatcherRole(): string
    {
        return $this->watcherRole;
    }

    public function setWatcherRole(string $watcherRole): void
    {
        $this->watcherRole = $watcherRole;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommitterRole(): string
    {
        return $this->committerRole;
    }

    public function setCommitterRole(string $committerRole): void
    {
        $this->committerRole = $committerRole;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminRole(): string
    {
        return $this->adminRole;
    }

    public function setAdminRole(string $adminRole): void
    {
        $this->adminRole = $adminRole;
    }
}
