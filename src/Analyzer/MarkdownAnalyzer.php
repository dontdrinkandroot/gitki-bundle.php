<?php

namespace Dontdrinkandroot\GitkiBundle\Analyzer;

use Dontdrinkandroot\GitkiBundle\Model\Document\AnalyzedDocument;
use Dontdrinkandroot\GitkiBundle\Service\Markdown\MarkdownServiceInterface;
use Dontdrinkandroot\Path\FilePath;

class MarkdownAnalyzer implements AnalyzerInterface
{
    public function __construct(private MarkdownServiceInterface $markdownService)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function supports(FilePath $filePath, ?string $mimeType): bool
    {
        if (in_array($mimeType, ['text/markdown', 'text/x-markdown'])) {
            return true;
        }

        if ($mimeType === 'text/plain' && 'md' === $filePath->getExtension()) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function analyze(FilePath $path, $content): AnalyzedDocument
    {
        $markdownDocument = $this->markdownService->parse($content, $path);
        $analyzedDocument = new AnalyzedDocument($path);
        $analyzedDocument->setTitle($markdownDocument->getTitle());
        $analyzedDocument->setContent($content);
        $analyzedDocument->setLinkedPaths($markdownDocument->getLinkedPaths());
        $analyzedDocument->setAnalyzedContent($content);

        return $analyzedDocument;
    }
}
