<?php

namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\GitkiBundle\Service\Directory\DirectoryServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\ExtensionRegistry\ExtensionRegistryInterface;
use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Role\RoleServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class BaseController extends Controller
{
    const ANONYMOUS_ROLE = 'IS_AUTHENTICATED_ANONYMOUSLY';

    /**
     * @return GitUserInterface|null
     */
    protected function findGitUser()
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
    protected function getGitUser()
    {
        $user = $this->findGitUser();
        if (null === $user) {
            throw new \Exception('No user was found');
        }

        return $user;
    }

    /**
     * @return WikiService
     */
    protected function getWikiService()
    {
        return $this->get('ddr.gitki.service.wiki');
    }

    /**
     * @return DirectoryServiceInterface
     */
    protected function getDirectoryService()
    {
        return $this->get('ddr.gitki.service.directory');
    }

    /**
     * @return RoleServiceInterface
     */
    protected function getRoleServie()
    {
        return $this->get('ddr.gitki.service.role');
    }

    /**
     * @return ExtensionRegistryInterface
     */
    protected function getExtensionRegistry()
    {
        return $this->get('ddr.gitki.registry.extension');
    }

    /**
     * @return FileSystemServiceInterface
     */
    protected function getFileSystemService()
    {
        return $this->get('ddr.gitki.service.file_system');
    }

    protected function assertWatcher()
    {
        if (!$this->isGranted($this->getRoleServie()->getWatcherRole())) {
            throw new AuthenticationException();
        }
    }

    protected function assertCommitter()
    {
        if (!$this->isGranted($this->getRoleServie()->getCommitterRole())) {
            throw new AuthenticationException();
        }
    }

    protected function assertAdmin()
    {
        if (!$this->isGranted($this->getRoleServie()->getAdminRole())) {
            throw new AuthenticationException();
        }
    }

    /**
     * Generate an etag based on the timestamp and the current user.
     *
     * @param \DateTime $timeStamp
     *
     * @return string The generated etag.
     */
    protected function generateEtag(\DateTime $timeStamp)
    {
        $user = $this->findGitUser();
        $userString = '';
        if (null !== $user) {
            $userString = $user->getGitUserName();
        }

        return md5($timeStamp->getTimestamp() . $userString);
    }
}
