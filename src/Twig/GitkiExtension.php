<?php

namespace Dontdrinkandroot\GitkiBundle\Twig;

use Dontdrinkandroot\GitkiBundle\Service\ExtensionRegistry\ExtensionRegistryInterface;
use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class GitkiExtension extends AbstractExtension
{
    public function __construct(private readonly ExtensionRegistryInterface $extensionRegistry)
    {
    }

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('dirTitle', $this->titleFilter(...)),
        ];
    }

    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('isEditable', $this->isEditable(...))
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

    public function isEditable(string $extension): bool
    {
        return $this->extensionRegistry->isEditable($extension);
    }
}
