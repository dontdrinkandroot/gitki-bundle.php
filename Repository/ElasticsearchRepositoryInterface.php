<?php

namespace Dontdrinkandroot\GitkiBundle\Repository;

use Dontdrinkandroot\GitkiBundle\Event\FileChangedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileDeletedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileMovedEvent;
use Dontdrinkandroot\GitkiBundle\Model\SearchResult;
use Dontdrinkandroot\Path\FilePath;

interface ElasticsearchRepositoryInterface
{

    /**
     * @param FileChangedEvent $event
     */
    public function onFileChanged(FileChangedEvent $event);

    /**
     * @param FileDeletedEvent $event
     */
    public function onFileDeleted(FileDeletedEvent $event);

    /**
     * @param FileMovedEvent $event
     */
    public function onFileMoved(FileMovedEvent $event);

    /**
     * @param $searchString
     *
     * @return SearchResult[]
     */
    public function search($searchString);

    /**
     * @param FilePath $path
     */
    public function addFile(FilePath $path);

    /**
     * @param FilePath $path
     */
    public function deleteFile(FilePath $path);

    /**
     * Clears the whole index.
     */
    public function clear();
}
