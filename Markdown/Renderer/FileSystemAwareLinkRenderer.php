<?php

namespace Dontdrinkandroot\GitkiBundle\Markdown\Renderer;

use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemServiceInterface;
use Dontdrinkandroot\GitkiBundle\Utils\StringUtils;
use Dontdrinkandroot\Path\FilePath;
use Exception;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;
use League\CommonMark\Inline\Renderer\LinkRenderer;
use League\CommonMark\Util\ConfigurationAwareInterface;
use League\CommonMark\Util\ConfigurationInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class FileSystemAwareLinkRenderer implements InlineRendererInterface, ConfigurationAwareInterface
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
    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        $htmlElement = $this->decoratedRenderer->render($inline, $htmlRenderer);

        if ($this->isExternalUrl($inline->getUrl())) {
            $htmlElement->setAttribute('rel', 'external');

            return $htmlElement;
        }

        if (!$this->targetUrlExists($inline->getUrl())) {
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
    public function setConfiguration(ConfigurationInterface $configuration)
    {
        $this->decoratedRenderer->setConfiguration($configuration);
    }
}
