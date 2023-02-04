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
     * @param iterable<AnalyzerInterface> $analyzers
     */
    public function __construct(
        private readonly GitServiceInterface $gitService,
        private readonly ElasticsearchRepositoryInterface $repository,
        private readonly iterable $analyzers,
        private readonly MimeTypeGuesserInterface $mimeTypeGuesser
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
        $this->indexFile($event->file);
    }

    /**
     * {@inheritdoc}
     */
    public function onFileRemoved(FileRemovedEvent $event): void
    {
        $this->deleteFile($event->file);
    }

    /**
     * {@inheritdoc}
     */
    public function onFileMoved(FileMovedEvent $event): void
    {
        $this->deleteFile($event->previousFile);
        $this->indexFile($event->file);
    }
}
