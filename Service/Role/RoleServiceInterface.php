<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Role;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
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
