<?php

namespace Dontdrinkandroot\GitkiBundle\Analyzer;

use Dontdrinkandroot\GitkiBundle\Model\Document\AnalyzedDocument;
use Dontdrinkandroot\Path\FilePath;
use Override;

class TextAnalyzer implements AnalyzerInterface
{
    #[Override]
    public function supports(FilePath $filePath, ?string $mimeType): bool
    {
        return $mimeType === 'text/plain';
    }

    #[Override]
    public function analyze(FilePath $path, $content): AnalyzedDocument
    {
        $analyzedFile = new AnalyzedDocument(
            path: $path,
            analyzedContent: $content,
            title: $path->getName(),
            content: $content
        );

        return $analyzedFile;
    }
}
