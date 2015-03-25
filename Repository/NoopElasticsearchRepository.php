<?php

namespace Dontdrinkandroot\GitkiBundle\Repository;

use Dontdrinkandroot\GitkiBundle\Event\FileChangedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileDeletedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileMovedEvent;
use Dontdrinkandroot\Path\FilePath;

class NoopElasticsearchRepository implements ElasticsearchRepositoryInterface
{

    /**
     * {@inheritdoc}
     */
    public function onFileChanged(FileChangedEvent $event)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    public function onFileDeleted(FileDeletedEvent $event)
    {
        /* NOOP */
    }

    /**
     * {@inheritdoc}
     */
    public function onFileMoved(FileMovedEvent $event)
    {
        /* NOOP */
    }

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
    public function addFile(FilePath $path)
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
