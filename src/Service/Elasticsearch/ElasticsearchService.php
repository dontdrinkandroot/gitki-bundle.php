<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Elasticsearch;

use Dontdrinkandroot\GitkiBundle\Analyzer\AnalyzerInterface;
use Dontdrinkandroot\GitkiBundle\Event\FileChangedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileMovedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileRemovedEvent;
use Dontdrinkandroot\GitkiBundle\Repository\ElasticsearchRepositoryInterface;
use Dontdrinkandroot\GitkiBundle\Service\Git\GitServiceInterface;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

class ElasticsearchService implements ElasticsearchServiceInterface
{
    /**
     * @param GitServiceInterface $gitService
     * @param ElasticsearchRepositoryInterface $repository
     * @param iterable<AnalyzerInterface> $analyzers
     * @param MimeTypeGuesserInterface $mimeTypeGuesser
     */
    public function __construct(
        private GitServiceInterface $gitService,
        private ElasticsearchRepositoryInterface $repository,
        private iterable $analyzers,
        private MimeTypeGuesserInterface $mimeTypeGuesser
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $searchString): array
    {
        return $this->repository->search($searchString);
    }

    /**
     * {@inheritdoc}
     */
    public function indexFile(FilePath $filePath): void
    {
        $mimeType = $this->mimeTypeGuesser->guessMimeType(
            $this->gitService->getAbsolutePath($filePath)->toAbsoluteFileSystemString()
        );
        foreach ($this->analyzers as $analyzer) {
            if ($analyzer->supports($filePath, $mimeType)) {
                $content = $this->gitService->getContent($filePath);
                $document = $analyzer->analyze($filePath, $content);

                $this->repository->indexFile($filePath, $document);

                return;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFile(FilePath $filePath): void
    {
        $this->repository->deleteFile($filePath);
    }

    /**
     * {@inheritdoc}
     */
    public function clearIndex(): void
    {
        $this->repository->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function onFileChanged(FileChangedEvent $event): void
    {
        $this->indexFile($event->getFile());
    }

    /**
     * {@inheritdoc}
     */
    public function onFileRemoved(FileRemovedEvent $event): void
    {
        $this->deleteFile($event->getFile());
    }

    /**
     * {@inheritdoc}
     */
    public function onFileMoved(FileMovedEvent $event): void
    {
        $this->deleteFile($event->getPreviousFile());
        $this->indexFile($event->getFile());
    }
}
