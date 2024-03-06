<?php

namespace Dontdrinkandroot\GitkiBundle\Analyzer;

use Dontdrinkandroot\GitkiBundle\Model\Document\AnalyzedDocument;
use Dontdrinkandroot\GitkiBundle\Service\Markdown\MarkdownServiceInterface;
use Dontdrinkandroot\Path\FilePath;
use Override;

class MarkdownAnalyzer implements AnalyzerInterface
{
    public function __construct(private readonly MarkdownServiceInterface $markdownService)
    {
    }

    #[Override]
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

    #[Override]
    public function analyze(FilePath $path, $content): AnalyzedDocument
    {
        $markdownDocument = $this->markdownService->parse($content, $path);
        $analyzedDocument = new AnalyzedDocument(
            path: $path,
            analyzedContent: $content,
            title: $markdownDocument->title,
            content: $content
        );
        $analyzedDocument->setLinkedPaths($markdownDocument->getLinkedPaths());

        return $analyzedDocument;
    }
}
