<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Markdown;

use Dontdrinkandroot\GitkiBundle\Markdown\Renderer\EscapingHtmlBlockRenderer;
use Dontdrinkandroot\GitkiBundle\Markdown\Renderer\EscapingHtmlInlineRenderer;
use Dontdrinkandroot\GitkiBundle\Markdown\Renderer\RepositoryAwareLinkRenderer;
use Dontdrinkandroot\GitkiBundle\Markdown\Renderer\TocBuildingHeaderRenderer;
use Dontdrinkandroot\GitkiBundle\Model\Document\ParsedMarkdownDocument;
use Dontdrinkandroot\GitkiBundle\Repository\GitRepository;
use Dontdrinkandroot\GitkiBundle\Repository\GitRepositoryInterface;
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
     * @param GitRepositoryInterface $repository
     * @param bool          $allowHtml
     */
    public function __construct(GitRepositoryInterface $repository, $allowHtml)
    {
        $this->repository = $repository;
        $this->allowHtml = $allowHtml;
    }

    /**
     * @param string   $content
     * @param FilePath $path
     *
     * @return ParsedMarkdownDocument
     */
    public function parse($content, FilePath $path)
    {
        $linkRenderer = new RepositoryAwareLinkRenderer($this->repository, $path);
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
