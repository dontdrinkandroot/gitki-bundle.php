<?php


namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\GitkiBundle\Service\Directory\DirectoryServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\ExtensionRegistry\ExtensionRegistryInterface;
use Dontdrinkandroot\GitkiBundle\Service\Role\RoleServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BaseController extends Controller
{

    const ANONYMOUS_ROLE = 'IS_AUTHENTICATED_ANONYMOUSLY';

    /**
     * @return GitUserInterface
     *
     * @throws \Exception Thrown if the current user is not a GitUser.
     */
    protected function getGitUser()
    {
        $user = $this->getUser();
        if (null === $user) {
            throw new \Exception('No user was found');
        }

        if (!($user instanceof GitUserInterface)) {
            throw new \Exception('Unexpected User Class');
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

    protected function assertRole($role)
    {
        if (false === $this->get('security.context')->isGranted($role)) {
            throw new AccessDeniedException();
        }
    }

    protected function assertWatcher()
    {
        $this->denyAccessUnlessGranted($this->getRoleServie()->getWatcherRole());
    }

    protected function assertCommitter()
    {
        $this->denyAccessUnlessGranted($this->getRoleServie()->getCommitterRole());
    }

    protected function assertAdmin()
    {
        $this->denyAccessUnlessGranted($this->getRoleServie()->getAdminRole());
    }

    /**
     * @param string $role
     *
     * @return bool
     */
    protected function hasRole($role)
    {
        return $this->get('security.context')->isGranted($role);
    }

    protected function getEnvironment()
    {
        /** @var Kernel $kernel */
        $kernel = $this->container->get('kernel');

        return $kernel->getEnvironment();
    }

    /**
     * @return ExtensionRegistryInterface
     */
    protected function getExtensionRegistry()
    {
        return $this->get('ddr.gitki.registry.extension');
    }

    protected function generateEtag(\DateTime $timeStamp)
    {
        $user = $this->getGitUser();
        $userString = '';
        if (null !== $user) {
            $userString = $user->getUsername();
        }

        return md5($timeStamp->getTimestamp() . $userString);
    }
}
