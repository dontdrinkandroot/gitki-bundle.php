<?php

namespace Dontdrinkandroot\GitkiBundle\Twig;

use Dontdrinkandroot\GitkiBundle\Service\ExtensionRegistry\ExtensionRegistryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class GitkiExtension extends AbstractExtension
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
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
}
