<?php


namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\GitkiBundle\Service\ExtensionRegistryInterface;
use Dontdrinkandroot\GitkiBundle\Service\WikiService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BaseController extends Controller
{

    const ANONYMOUS_ROLE = 'IS_AUTHENTICATED_ANONYMOUSLY';

    /**
     * @return GitUserInterface|null
     *
     * @throws \Exception Thrown if the current user is not a GitUser.
     */
    protected function getGitUser()
    {
        $user = parent::getUser();
        if (null === $user) {
            return null;
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

    protected function assertRole($role)
    {
        if (false === $this->get('security.context')->isGranted($role)) {
            throw new AccessDeniedException();
        }
    }

    protected function assertWatcher()
    {
        $this->denyAccessUnlessGranted($this->getWikiService()->getWatcherRole());
    }

    protected function assertCommitter()
    {
        $this->denyAccessUnlessGranted($this->getWikiService()->getCommitterRole());
    }

    protected function assertAdmin()
    {
        $this->denyAccessUnlessGranted($this->getWikiService()->getAdminRole());
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
}
