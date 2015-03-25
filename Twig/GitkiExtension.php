<?php


namespace Dontdrinkandroot\GitkiBundle\Twig;

use Dontdrinkandroot\GitkiBundle\Service\ExtensionRegistry\ExtensionRegistryInterface;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
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

    /**
     * @var ExtensionRegistryInterface
     */
    private $extensionRegistry;

    public function __construct(
        SecurityContextInterface $securityContext,
        WikiService $wikiService,
        ExtensionRegistryInterface $extensionRegistry
    ) {
        $this->wikiService = $wikiService;
        $this->securityContext = $securityContext;
        $this->extensionRegistry = $extensionRegistry;
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
            new \Twig_SimpleFunction('isGitkiAdmin', [$this, 'isAdmin']),
            new \Twig_SimpleFunction('isEditable', [$this, 'isEditable'])
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

    public function isEditable($extension)
    {
        return $this->extensionRegistry->isEditable($extension);
    }
}
