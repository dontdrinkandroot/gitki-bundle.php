<?php

namespace Dontdrinkandroot\GitkiBundle\Repository;

use Dontdrinkandroot\GitkiBundle\Analyzer\AnalyzedFile;
use Dontdrinkandroot\GitkiBundle\Model\SearchResult;
use Dontdrinkandroot\Path\FilePath;

interface ElasticsearchRepositoryInterface
{

    /**
     * @param $searchString
     *
     * @return SearchResult[]
     */
    public function search($searchString);

    /**
     * @param FilePath     $path
     * @param AnalyzedFile $analyzedFile
     */
    public function indexFile(FilePath $path, AnalyzedFile $analyzedFile);

    /**
     * @param FilePath $path
     */
    public function deleteFile(FilePath $path);

    /**
     * Clears the whole index.
     */
    public function clear();
}
