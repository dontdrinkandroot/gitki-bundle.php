<?php


namespace Dontdrinkandroot\GitkiBundle\Service;

use Dontdrinkandroot\GitkiBundle\Event\FileChangedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileDeletedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileMovedEvent;
use Dontdrinkandroot\GitkiBundle\Model\SearchResult;
use Dontdrinkandroot\Path\FilePath;

class NoopElasticsearchService implements ElasticsearchServiceInterface
{

    /**
     * @param string $searchString
     *
     * @return SearchResult[]
     */
    public function search($searchString)
    {
        return [];
    }

    /**
     * @param FilePath $filePath
     */
    public function indexFile(FilePath $filePath)
    {
        /* Noop */
    }

    /**
     * @param FilePath $filePath
     */
    public function deleteFile(FilePath $filePath)
    {
        /* Noop */
    }

    public function onFileChanged(FileChangedEvent $event)
    {
        /* Noop */
        echo "hello world";
    }

    public function onFileDeleted(FileDeletedEvent $event)
    {
        /* Noop */
        echo "hello world";
    }

    public function onFileMoved(FileMovedEvent $event)
    {
        /* Noop */
        echo "hello world";
    }
}
