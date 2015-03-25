<?php


namespace Dontdrinkandroot\GitkiBundle\Service;

use Dontdrinkandroot\GitkiBundle\Model\SearchResult;
use Dontdrinkandroot\Path\FilePath;

interface ElasticsearchServiceInterface
{

    /**
     * @param string $searchString
     *
     * @return SearchResult[]
     */
    public function search($searchString);

    /**
     * @param FilePath $filePath
     */
    public function indexFile(FilePath $filePath);

    /**
     * @param FilePath $filePath
     */
    public function deleteFile(FilePath $filePath);

    /**
     * Removes all document from the index.
     */
    public function clearIndex();
}
