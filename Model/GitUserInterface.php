<?php

namespace Dontdrinkandroot\GitkiBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

interface GitUserInterface extends UserInterface
{
    /**
     * @return string
     */
    public function getUsername();

    /**
     * @return string
     */
    public function getEmail();
}
