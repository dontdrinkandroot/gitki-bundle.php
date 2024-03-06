<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Elasticsearch;

use Dontdrinkandroot\GitkiBundle\Event\FileChangedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileMovedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileRemovedEvent;
use Dontdrinkandroot\Path\FilePath;
use Override;

class NoopElasticsearchService implements ElasticsearchServiceInterface
{
    #[Override]
    public function search(string $searchString): array
    {
        return [];
    }

    #[Override]
    public function indexFile(FilePath $filePath): void
    {
        /* Noop */
    }

    #[Override]
    public function deleteFile(FilePath $filePath): void
    {
        /* Noop */
    }

    #[Override]
    public function clearIndex(): void
    {
        /* Noop */
    }

    #[Override]
    public function onFileChanged(FileChangedEvent $event): void
    {
        /* Noop */
    }

    #[Override]
    public function onFileRemoved(FileRemovedEvent $event): void
    {
        /* Noop */
    }

    #[Override]
    public function onFileMoved(FileMovedEvent $event): void
    {
        /* Noop */
    }
}
