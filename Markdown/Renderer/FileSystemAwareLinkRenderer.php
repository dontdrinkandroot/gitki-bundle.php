<?php

namespace Dontdrinkandroot\GitkiBundle\Markdown\Renderer;

use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemServiceInterface;
use Dontdrinkandroot\GitkiBundle\Utils\StringUtils;
use Dontdrinkandroot\Path\FilePath;
use Exception;
use League\CommonMark\Extension\CommonMark\Node\Inline\AbstractWebResource;
use League\CommonMark\Extension\CommonMark\Renderer\Inline\LinkRenderer;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\Config\ConfigurationAwareInterface;
use League\Config\ConfigurationInterface;
use Stringable;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class FileSystemAwareLinkRenderer implements NodeRendererInterface, ConfigurationAwareInterface
{
    /** @var FileSystemServiceInterface */
    private $fileSystemService;

    private $linkedPaths = [];

    /** @var FilePath */
    private $currentFilePath;

    /** @var LinkRenderer */
    private $decoratedRenderer;

    public function __construct(FileSystemServiceInterface $fileSystemService, FilePath $currentFilePath)
    {
        $this->fileSystemService = $fileSystemService;
        $this->currentFilePath = $currentFilePath;
        $this->decoratedRenderer = new LinkRenderer();
    }

    /**
     * {@inheritdoc}
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): Stringable|string|null
    {
        assert($node instanceof AbstractWebResource);
        $htmlElement = $this->decoratedRenderer->render($node, $childRenderer);

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

    protected function isExternalUrl($url)
    {
        try {
            $urlParts = parse_url($url);
            if (array_key_exists('scheme', $urlParts)) {
                return true;
            }
            if (array_key_exists('host', $urlParts)) {
                return true;
            }
        } catch (Exception $e) {
            /* If parsing url fails, ignore silently and assume it is not external */
        }

        return false;
    }

    protected function targetUrlExists($url)
    {
        try {
            $urlParts = parse_url($url);

            $urlPath = $urlParts['path'];
            $path = null;
            if (StringUtils::startsWith($urlPath, '/')) {
                /* Absolute paths won't work */
                return false;
            }

            $urlPath = urldecode($urlPath);

            $currentDirectoryPath = $this->currentFilePath->getParentPath();
            $path = $currentDirectoryPath->appendPathString($urlPath);
            $fileExists = $this->fileSystemService->exists($path);

            $this->linkedPaths[] = $path;

            return $fileExists;
        } catch (Exception $e) {
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
