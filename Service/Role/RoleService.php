<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Role;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class RoleService implements RoleServiceInterface
{
    /**
     * @var string
     */
    protected $watcherRole = 'IS_AUTHENTICATED_ANONYMOUSLY';

    /**
     * @var string
     */
    protected $committerRole = 'ROLE_USER';

    /**
     * @var string
     */
    protected $adminRole = 'ROLE_ADMIN';

    /**
     * {@inheritdoc}
     */
    public function getWatcherRole()
    {
        return $this->watcherRole;
    }

    /**
     * @param string $watcherRole
     *
     * @return null
     */
    public function setWatcherRole($watcherRole)
    {
        $this->watcherRole = $watcherRole;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommitterRole()
    {
        return $this->committerRole;
    }

    /**
     * @param string $committerRole
     *
     * @return null
     */
    public function setCommitterRole($committerRole)
    {
        $this->committerRole = $committerRole;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminRole()
    {
        return $this->adminRole;
    }

    /**
     * @param string $adminRole
     *
     * @return null
     */
    public function setAdminRole($adminRole)
    {
        $this->adminRole = $adminRole;
    }
}
