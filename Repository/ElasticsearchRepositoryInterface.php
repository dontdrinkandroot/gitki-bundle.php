<?php

namespace Dontdrinkandroot\GitkiBundle\Repository;

use Dontdrinkandroot\GitkiBundle\Model\Document\AnalyzedDocument;
use Dontdrinkandroot\GitkiBundle\Model\Document\SearchResultDocument;
use Dontdrinkandroot\Path\FilePath;

interface ElasticsearchRepositoryInterface
{
    /**
     * @param string $searchString
     *
     * @return list<SearchResultDocument>
     */
    public function search(string $searchString): array;

    public function indexFile(FilePath $path, AnalyzedDocument $document);

    public function deleteFile(FilePath $path);

    public function findTitle(FilePath $path): ?string;

    /**
     * Clears the whole index.
     */
    public function clear();
}
