<?php

namespace Dontdrinkandroot\GitkiBundle\Analyzer;

use Dontdrinkandroot\GitkiBundle\Model\Document\AnalyzedDocument;
use Dontdrinkandroot\Path\FilePath;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
interface AnalyzerInterface
{
    /**
     * @return string[]
     */
    public function getSupportedExtensions();

    /**
     * @param FilePath $path
     * @param string   $content
     *
     * @return AnalyzedDocument
     */
    public function analyze(FilePath $path, $content);
}
