<?php


namespace Dontdrinkandroot\GitkiBundle\Analyzer;

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
        $analyzedFile = new AnalyzedFile();
        $analyzedFile->setContent($content);
        $analyzedFile->setTitle($path->getName());

        return $analyzedFile;
    }
}
