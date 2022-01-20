<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Elasticsearch;

use Dontdrinkandroot\GitkiBundle\Event\Listener\FileChangedEventListenerInterface;
use Dontdrinkandroot\GitkiBundle\Event\Listener\FileMovedEventListenerInterface;
use Dontdrinkandroot\GitkiBundle\Event\Listener\FileRemovedEventListenerInterface;
use Dontdrinkandroot\GitkiBundle\Model\Document\SearchResultDocument;
use Dontdrinkandroot\Path\FilePath;

interface ElasticsearchServiceInterface
    extends FileChangedEventListenerInterface, FileMovedEventListenerInterface, FileRemovedEventListenerInterface
{
    /** @return list<SearchResultDocument> */
    public function search(string $searchString): array;

    public function indexFile(FilePath $filePath): void;

    public function deleteFile(FilePath $filePath): void;

    /**
     * Removes all document from the index.
     */
    public function clearIndex(): void;
}
