<?php

namespace Dontdrinkandroot\GitkiBundle\Markdown\Renderer;

use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemServiceInterface;
use Dontdrinkandroot\Path\FilePath;
use Exception;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\CommonMark\Renderer\Inline\LinkRenderer;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;
use Stringable;

class FileSystemAwareLinkRenderer implements NodeRendererInterface, ConfigurationAwareInterface
{
    private array $linkedPaths = [];

    private readonly LinkRenderer $decoratedRenderer;

    public function __construct(
        private readonly FileSystemServiceInterface $fileSystemService,
        private readonly FilePath $currentFilePath
    ) {
        $this->decoratedRenderer = new LinkRenderer();
    }

    /**
     * {@inheritdoc}
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): Stringable|string|null
    {
        assert($node instanceof Link);
        $htmlElement = $this->decoratedRenderer->render($node, $childRenderer);
        assert($htmlElement instanceof HtmlElement);

        if ($this->isExternalUrl($node->getUrl())) {
            $htmlElement->setAttribute('rel', 'external');

            return $htmlElement;
        }

        if (!$this->targetUrlExists($node->getUrl())) {
            $classes = $htmlElement->getAttribute('class');
            if (null === $classes) {
                $classes = '';
            } else {
                $classes .= ' ';
            }
            $classes .= 'missing';
            $htmlElement->setAttribute('class', $classes);
        }

        return $htmlElement;
    }

    protected function isExternalUrl(string $url): bool
    {
        try {
            $urlParts = parse_url($url);
            if (array_key_exists('scheme', $urlParts)) {
                return true;
            }
            if (array_key_exists('host', $urlParts)) {
                return true;
            }
        } catch (Exception) {
            /* If parsing url fails, ignore silently and assume it is not external */
        }

        return false;
    }

    protected function targetUrlExists(string $url): bool
    {
        try {
            $urlParts = parse_url($url);

            if (!isset($urlParts['path'])) {
                return true;
            }
            $urlPath = $urlParts['path'];
            $path = null;
            if (str_starts_with($urlPath, '/')) {
                /* Absolute paths won't work */
                return false;
            }

            $urlPath = urldecode($urlPath);

            $currentDirectoryPath = $this->currentFilePath->getParentPath();
            $path = $currentDirectoryPath->appendPathString($urlPath);
            $fileExists = $this->fileSystemService->exists($path);

            $this->linkedPaths[] = $path;

            return $fileExists;
        } catch (Exception) {
            /* If parsing url fails, ignore silently and assume the target exists */
        }

        return true;
    }

    /**
     * @return array
     */
    public function getLinkedPaths()
    {
        return $this->linkedPaths;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(ConfigurationInterface $configuration): void
    {
        $this->decoratedRenderer->setConfiguration($configuration);
    }
}
