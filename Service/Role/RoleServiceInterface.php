<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Role;

/**
 * @deprecated Use Voters instead
 */
interface RoleServiceInterface
{
    public function getWatcherRole(): string;

    public function getCommitterRole(): string;

    public function getAdminRole(): string;
}
