<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Role;

interface RoleServiceInterface
{

    /**
     * @return string
     */
    public function getWatcherRole();

    /**
     * @return string
     */
    public function getCommitterRole();

    /**
     * @return string
     */
    public function getAdminRole();
}
