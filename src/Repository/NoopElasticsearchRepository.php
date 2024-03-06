<?php

namespace Dontdrinkandroot\GitkiBundle\Repository;

use Dontdrinkandroot\GitkiBundle\Model\Document\AnalyzedDocument;
use Dontdrinkandroot\Path\FilePath;
use Override;

class NoopElasticsearchRepository implements ElasticsearchRepositoryInterface
{
    #[Override]
    public function search(string $searchString): array
    {
        return [];
    }

    #[Override]
    public function indexFile(FilePath $path, AnalyzedDocument $document): mixed
    {
        return [];
    }

    #[Override]
    public function deleteFile(FilePath $path): mixed
    {
        return [];
    }

    #[Override]
    public function clear(): void
    {
        /* NOOP */
    }

    #[Override]
    public function findTitle(FilePath $path): ?string
    {
        return null;
    }
}
