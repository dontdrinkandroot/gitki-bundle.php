<?php

namespace Dontdrinkandroot\GitkiBundle\Repository;

use Dontdrinkandroot\GitkiBundle\Model\Document\AnalyzedDocument;
use Dontdrinkandroot\GitkiBundle\Model\Document\SearchResultDocument;
use Dontdrinkandroot\Path\FilePath;

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
     * Clears the whole index.
     */
    public function clear();
}
