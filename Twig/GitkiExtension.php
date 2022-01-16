<?php

namespace Dontdrinkandroot\GitkiBundle\Twig;

use Dontdrinkandroot\GitkiBundle\Service\ExtensionRegistry\ExtensionRegistryInterface;
use Dontdrinkandroot\GitkiBundle\Service\Role\RoleServiceInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class GitkiExtension extends AbstractExtension
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
        private RoleServiceInterface $roleService,
        private ExtensionRegistryInterface $extensionRegistry
    ) {
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'gitki_extension';
    }

    /**
     * @inheritdoc
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('dirTitle', [$this, 'titleFilter']),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('isGitkiWatcher', [$this, 'isWatcher']),
            new TwigFunction('isGitkiCommitter', [$this, 'isCommitter']),
            new TwigFunction('isGitkiAdmin', [$this, 'isAdmin']),
            new TwigFunction('isEditable', [$this, 'isEditable'])
        ];
    }

    public function titleFilter(string $title): string
    {
        $words = explode('_', $title);
        $transformedTitle = '';
        for ($i = 0; $i < count($words) - 1; $i++) {
            $transformedTitle .= ucfirst($words[$i]) . ' ';
        }
        $transformedTitle .= ucfirst($words[count($words) - 1]);

        return $transformedTitle;
    }

    public function isWatcher(): bool
    {
        return $this->hasRole($this->roleService->getWatcherRole());
    }

    public function isCommitter(): bool
    {
        return $this->hasRole($this->roleService->getCommitterRole());
    }

    public function isAdmin(): bool
    {
        return $this->hasRole($this->roleService->getAdminRole());
    }

    public function hasRole(string $role): bool
    {
        return $this->authorizationChecker->isGranted($role);
    }

    public function isEditable(string $extension): bool
    {
        return $this->extensionRegistry->isEditable($extension);
    }
}
