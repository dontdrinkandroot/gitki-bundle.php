<?php

namespace Dontdrinkandroot\GitkiBundle\Repository;

use Dontdrinkandroot\GitkiBundle\Model\Document\AnalyzedDocument;
use Dontdrinkandroot\Path\FilePath;

class NoopElasticsearchRepository implements ElasticsearchRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function search(string $searchString): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function indexFile(FilePath $path, AnalyzedDocument $document): mixed
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFile(FilePath $path): mixed
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    public function findTitle(FilePath $path): ?string
    {
        return null;
    }
}
