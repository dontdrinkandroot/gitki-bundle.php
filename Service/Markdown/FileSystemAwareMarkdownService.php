<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Markdown;

use Dontdrinkandroot\GitkiBundle\Markdown\Renderer\BootstrapTableRenderer;
use Dontdrinkandroot\GitkiBundle\Markdown\Renderer\EscapingHtmlBlockRenderer;
use Dontdrinkandroot\GitkiBundle\Markdown\Renderer\EscapingHtmlInlineRenderer;
use Dontdrinkandroot\GitkiBundle\Markdown\Renderer\FileSystemAwareLinkRenderer;
use Dontdrinkandroot\GitkiBundle\Markdown\Renderer\TocBuildingHeaderRenderer;
use Dontdrinkandroot\GitkiBundle\Model\Document\ParsedMarkdownDocument;
use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemServiceInterface;
use Dontdrinkandroot\Path\FilePath;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer;
use Webuni\CommonMark\TableExtension\TableExtension;

class FileSystemAwareMarkdownService implements MarkdownServiceInterface
{

    /**
     * @var FileSystemServiceInterface
     */
    protected $fileSystemService;

    /**
     * @var bool
     */
    private $allowHtml;

    /**
     * @param FileSystemServiceInterface $fileSystemService
     * @param bool                       $allowHtml
     */
    public function __construct(FileSystemServiceInterface $fileSystemService, $allowHtml)
    {
        $this->fileSystemService = $fileSystemService;
        $this->allowHtml = $allowHtml;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($content, FilePath $path)
    {
        $linkRenderer = new FileSystemAwareLinkRenderer($this->fileSystemService, $path);
        $headerRenderer = new TocBuildingHeaderRenderer();

        $environment = Environment::createCommonMarkEnvironment();
        $environment->addExtension(new TableExtension());
        $environment->addBlockRenderer('Webuni\CommonMark\TableExtension\Table', new BootstrapTableRenderer());
        $environment->addInlineRenderer('League\CommonMark\Inline\Element\Link', $linkRenderer);
        $environment->addBlockRenderer('League\CommonMark\Block\Element\Header', $headerRenderer);

        if (!$this->allowHtml) {
            $environment->addBlockRenderer(
                'League\CommonMark\Block\Element\HtmlBlock',
                new EscapingHtmlBlockRenderer()
            );
            $environment->addInlineRenderer('League\CommonMark\Inline\Element\Html', new EscapingHtmlInlineRenderer());
        }

        $parser = new DocParser($environment);
        $htmlRenderer = new HtmlRenderer($environment);
        $documentAST = $parser->parse($content);
        $html = $htmlRenderer->renderBlock($documentAST);

        $linkedPaths = $linkRenderer->getLinkedPaths();
        $title = $headerRenderer->getTitle();
        $toc = $headerRenderer->getToc();

        $result = new ParsedMarkdownDocument($path);
        $result->setContent($content);
        $result->setLinkedPaths($linkedPaths);
        $result->setTitle($title);
        $result->setToc($toc);
        $result->setHtml($html);

        return $result;
    }
}
