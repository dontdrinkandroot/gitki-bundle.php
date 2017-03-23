<?php

namespace Dontdrinkandroot\GitkiBundle\Repository;

use Dontdrinkandroot\GitkiBundle\Model\Document\AnalyzedDocument;
use Dontdrinkandroot\GitkiBundle\Model\Document\SearchResultDocument;
use Dontdrinkandroot\Path\FilePath;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
interface ElasticsearchRepositoryInterface
{
    /**
     * @param $searchString
     *
     * @return SearchResultDocument[]
     */
    public function search($searchString);

    /**
     * @param FilePath         $path
     * @param AnalyzedDocument $document
     */
    public function indexFile(FilePath $path, AnalyzedDocument $document);

    /**
     * @param FilePath $path
     */
    public function deleteFile(FilePath $path);

    /**
     * @param FilePath $path
     *
     * @return string
     */
    public function findTitle(FilePath $path);

    /**
     * Clears the whole index.
     */
    public function clear();
}
