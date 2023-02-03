<?php

namespace Dontdrinkandroot\GitkiBundle\Model;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
interface GitUserInterface
{
    /**
     * Get the full user name that is to be displayed in commits.
     *
     * @return string
     */
    public function getGitUserName();

    /**
     * Get the email address that is to be displayed in commits.
     *
     * @return string
     */
    public function getGitUserEmail();
}
