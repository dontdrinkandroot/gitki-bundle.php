<?php

namespace Dontdrinkandroot\GitkiBundle\Repository;

use Dontdrinkandroot\GitkiBundle\Model\Document\AnalyzedDocument;
use Dontdrinkandroot\GitkiBundle\Model\Document\SearchResultDocument;
use Dontdrinkandroot\Path\FilePath;

interface ElasticsearchRepositoryInterface
{
    /** @return list<SearchResultDocument> */
    public function search(string $searchString): array;

    public function indexFile(FilePath $path, AnalyzedDocument $document): mixed;

    public function deleteFile(FilePath $path): mixed;

    public function findTitle(FilePath $path): ?string;

    /**
     * Clears the whole index.
     */
    public function clear(): void;
}
