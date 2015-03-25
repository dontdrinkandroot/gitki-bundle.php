<?php


namespace Dontdrinkandroot\GitkiBundle\Service;

use Dontdrinkandroot\GitkiBundle\Event\FileChangedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileDeletedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileMovedEvent;
use Dontdrinkandroot\Path\FilePath;

class NoopElasticsearchService implements ElasticsearchServiceInterface
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
    public function indexFile(FilePath $filePath)
    {
        /* Noop */
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFile(FilePath $filePath)
    {
        /* Noop */
    }

    /**
     * {@inheritdoc}
     */
    public function clearIndex()
    {
        /* Noop */
    }

    public function onFileChanged(FileChangedEvent $event)
    {
        /* Noop */
    }

    public function onFileDeleted(FileDeletedEvent $event)
    {
        /* Noop */
    }

    public function onFileMoved(FileMovedEvent $event)
    {
        /* Noop */
    }
}
