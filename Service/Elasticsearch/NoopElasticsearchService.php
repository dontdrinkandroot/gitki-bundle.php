<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Elasticsearch;

use Dontdrinkandroot\GitkiBundle\Event\FileChangedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileMovedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileRemovedEvent;
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

    /**
     * {@inheritdoc}
     */
    public function onFileChanged(FileChangedEvent $event)
    {
        /* Noop */
    }

    /**
     * {@inheritdoc}
     */
    public function onFileRemoved(FileRemovedEvent $event)
    {
        /* Noop */
    }

    /**
     * {@inheritdoc}
     */
    public function onFileMoved(FileMovedEvent $event)
    {
        /* Noop */
    }
}
