<?php


namespace Dontdrinkandroot\GitkiBundle\Service;

use Dontdrinkandroot\GitkiBundle\Analyzer\AnalyzerInterface;
use Dontdrinkandroot\GitkiBundle\Event\FileChangedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileDeletedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileMovedEvent;
use Dontdrinkandroot\GitkiBundle\Repository\ElasticsearchRepositoryInterface;
use Dontdrinkandroot\GitkiBundle\Repository\GitRepositoryInterface;
use Dontdrinkandroot\Path\FilePath;

class ElasticsearchService implements ElasticsearchServiceInterface
{

    /**
     * @var AnalyzerInterface[];
     */
    protected $analyzers = [];

    /**
     * @var ElasticsearchRepositoryInterface
     */
    private $repository;

    /**
     * @var GitRepositoryInterface
     */
    private $gitRepository;

    public function __construct(GitRepositoryInterface $gitRepository, ElasticsearchRepositoryInterface $repository)
    {
        $this->gitRepository = $gitRepository;
        $this->repository = $repository;
    }

    /**
     * @inheritdoc
     */
    public function search($searchString)
    {
        return $this->repository->search($searchString);
    }

    /**
     * @inheritdoc
     */
    public function indexFile(FilePath $filePath)
    {
        if (!isset($this->analyzers[$filePath->getExtension()])) {
            return null;
        }

        $analyzer = $this->analyzers[$filePath->getExtension()];
        $content = $this->gitRepository->getContent($filePath);
        $analyzedFile = $analyzer->analyze($filePath, $content);

        return $this->repository->indexFile($filePath, $analyzedFile);
    }

    /**
     * @inheritdoc
     */
    public function deleteFile(FilePath $filePath)
    {
        $this->repository->deleteFile($filePath);
    }

    public function onFileChanged(FileChangedEvent $event)
    {
        $this->indexFile($event->getFile());
    }

    public function onFileDeleted(FileDeletedEvent $event)
    {
        $this->deleteFile($event->getFile());
    }

    public function onFileMoved(FileMovedEvent $event)
    {
        $this->deleteFile($event->getPreviousFile());
        $this->indexFile($event->getFile());
    }

    /**
     * @param AnalyzerInterface $analyzer
     */
    public function registerAnalyzer(AnalyzerInterface $analyzer)
    {
        foreach ($analyzer->getSupportedExtensions() as $extension) {
            $this->analyzers[$extension] = $analyzer;
        }
    }
}
