<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Elasticsearch;

use Dontdrinkandroot\GitkiBundle\Model\Document\SearchResultDocument;
use Dontdrinkandroot\Path\FilePath;

interface ElasticsearchServiceInterface
{

    /**
     * @param string $searchString
     *
     * @return SearchResultDocument[]
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
