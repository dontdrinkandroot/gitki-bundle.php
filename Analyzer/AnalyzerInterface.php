<?php

namespace Dontdrinkandroot\GitkiBundle\Analyzer;

use Dontdrinkandroot\GitkiBundle\Model\Document\AnalyzedDocument;
use Dontdrinkandroot\Path\FilePath;

interface AnalyzerInterface
{
    public function supports(FilePath $filePath, ?string $mimeType): bool;

    public function analyze(FilePath $path, string $content): AnalyzedDocument;
}
