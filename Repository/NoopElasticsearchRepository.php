<?php

namespace Dontdrinkandroot\GitkiBundle\Repository;

use Dontdrinkandroot\GitkiBundle\Model\Document\AnalyzedDocument;
use Dontdrinkandroot\Path\FilePath;

class NoopElasticsearchRepository implements ElasticsearchRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function search($searchString)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function indexFile(FilePath $path, AnalyzedDocument $document)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFile(FilePath $path)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    public function findTitle(FilePath $path)
    {
        return null;
    }
}
