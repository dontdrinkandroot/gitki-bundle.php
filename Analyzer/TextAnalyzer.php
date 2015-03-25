<?php


namespace Dontdrinkandroot\GitkiBundle\Analyzer;

use Dontdrinkandroot\GitkiBundle\Model\Document\AnalyzedDocument;
use Dontdrinkandroot\Path\FilePath;

class TextAnalyzer implements AnalyzerInterface
{

    /**
     * {@inheritdoc}
     */
    public function getSupportedExtensions()
    {
        return ['txt'];
    }

    /**
     * {@inheritdoc}
     */
    public function analyze(FilePath $path, $content)
    {
        $analyzedFile = new AnalyzedDocument($path);
        $analyzedFile->setContent($content);
        $analyzedFile->setAnalyzedContent($content);
        $analyzedFile->setTitle($path->getName());

        return $analyzedFile;
    }
}
