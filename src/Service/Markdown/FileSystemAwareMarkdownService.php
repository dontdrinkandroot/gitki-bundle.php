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
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\Extension\CommonMark\Node\Inline\HtmlInline;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Parser\MarkdownParser;
use League\CommonMark\Renderer\HtmlRenderer;

class FileSystemAwareMarkdownService implements MarkdownServiceInterface
{
    public function __construct(
        protected FileSystemServiceInterface $fileSystemService,
        private readonly bool $allowHtml
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $content, FilePath $path): ParsedMarkdownDocument
    {
        $linkRenderer = new FileSystemAwareLinkRenderer($this->fileSystemService, $path);
        $headerRenderer = new TocBuildingHeaderRenderer();

        $environment = new Environment();
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new TableExtension());
        $environment->addRenderer(Table::class, new BootstrapTableRenderer());
        $environment->addRenderer(Link::class, $linkRenderer);
        $environment->addRenderer(Heading::class, $headerRenderer);

        if (!$this->allowHtml) {
            $environment->addRenderer(HtmlBlock::class, new EscapingHtmlBlockRenderer());
            $environment->addRenderer(HtmlInline::class, new EscapingHtmlInlineRenderer());
        }

        $parser = new MarkdownParser($environment);
        $htmlRenderer = new HtmlRenderer($environment);
        $documentAST = $parser->parse($content);
        $html = $htmlRenderer->renderDocument($documentAST)->getContent();

        $linkedPaths = $linkRenderer->getLinkedPaths();
        $title = $headerRenderer->getTitle();
        $toc = $headerRenderer->getToc();

        $result = new ParsedMarkdownDocument(
            path: $path,
            toc: $toc,
            html: $html,
            title: $title,
            content: $content,
        );
        $result->setLinkedPaths($linkedPaths);

        return $result;
    }
}
