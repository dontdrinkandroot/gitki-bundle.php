<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Markdown;

use Dontdrinkandroot\GitkiBundle\MarkdownRenderer\EscapingHtmlBlockRenderer;
use Dontdrinkandroot\GitkiBundle\MarkdownRenderer\EscapingHtmlInlineRenderer;
use Dontdrinkandroot\GitkiBundle\MarkdownRenderer\RepositoryAwareLinkRenderer;
use Dontdrinkandroot\GitkiBundle\MarkdownRenderer\TocBuildingHeaderRenderer;
use Dontdrinkandroot\GitkiBundle\Model\Document\ParsedMarkdownDocument;
use Dontdrinkandroot\GitkiBundle\Repository\GitRepository;
use Dontdrinkandroot\Path\FilePath;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer;

class RepositoryAwareMarkdownService implements MarkdownServiceInterface
{

    /**
     * @var GitRepository
     */
    protected $repository;

    /**
     * @var bool
     */
    private $allowHtml;

    /**
     * @param GitRepository $repository
     * @param bool          $allowHtml
     */
    public function __construct(GitRepository $repository, $allowHtml)
    {
        $this->repository = $repository;
        $this->allowHtml = $allowHtml;
    }

    /**
     * @param FilePath $path
     * @param string   $content
     *
     * @return ParsedMarkdownDocument
     */
    public function parse(FilePath $path, $content)
    {

        $linkRenderer = new RepositoryAwareLinkRenderer($path, $this->repository);
        $headerRenderer = new TocBuildingHeaderRenderer();

        $environment = Environment::createCommonMarkEnvironment();
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
