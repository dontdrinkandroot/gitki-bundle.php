<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Twig;

use Dontdrinkandroot\Gitki\BaseBundle\Service\WikiService;
use Symfony\Component\Security\Core\SecurityContextInterface;

class GitkiExtension extends \Twig_Extension
{

    /**
     * @var WikiService
     */
    protected $wikiService;

    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    public function __construct(SecurityContextInterface $securityContext, WikiService $wikiService)
    {
        $this->wikiService = $wikiService;
        $this->securityContext = $securityContext;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'gitki_extension';
    }

    /**
     * @inheritdoc
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('dirTitle', [$this, 'titleFilter']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('isGitkiWatcher', [$this, 'isWatcher']),
            new \Twig_SimpleFunction('isGitkiCommitter', [$this, 'isCommitter']),
            new \Twig_SimpleFunction('isGitkiAdmin', [$this, 'isAdmin'])
        ];
    }

    public function titleFilter($title)
    {
        $words = explode('_', $title);
        $transformedTitle = '';
        for ($i = 0; $i < count($words) - 1; $i++) {
            $transformedTitle .= ucfirst($words[$i]) . ' ';
        }
        $transformedTitle .= ucfirst($words[count($words) - 1]);

        return $transformedTitle;
    }

    public function isWatcher()
    {
        return $this->hasRole($this->wikiService->getWatcherRole());
    }

    public function isCommitter()
    {
        return $this->hasRole($this->wikiService->getCommitterRole());
    }

    public function isAdmin()
    {
        return $this->hasRole($this->wikiService->getAdminRole());
    }

    public function hasRole($role)
    {
        return $this->securityContext->isGranted($role);
    }
}
