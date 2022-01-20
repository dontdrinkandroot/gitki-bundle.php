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
    public function search(string $searchString): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function indexFile(FilePath $filePath): void
    {
        /* Noop */
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFile(FilePath $filePath): void
    {
        /* Noop */
    }

    /**
     * {@inheritdoc}
     */
    public function clearIndex(): void
    {
        /* Noop */
    }

    /**
     * {@inheritdoc}
     */
    public function onFileChanged(FileChangedEvent $event): void
    {
        /* Noop */
    }

    /**
     * {@inheritdoc}
     */
    public function onFileRemoved(FileRemovedEvent $event): void
    {
        /* Noop */
    }

    /**
     * {@inheritdoc}
     */
    public function onFileMoved(FileMovedEvent $event): void
    {
        /* Noop */
    }
}
