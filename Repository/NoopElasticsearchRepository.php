<?php

namespace Dontdrinkandroot\GitkiBundle\Repository;

use Dontdrinkandroot\GitkiBundle\Analyzer\AnalyzedFile;
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
    public function indexFile(FilePath $path, AnalyzedFile $analyzedFile)
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
}
