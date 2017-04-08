<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Elasticsearch;

use Dontdrinkandroot\GitkiBundle\Analyzer\AnalyzerInterface;
use Dontdrinkandroot\GitkiBundle\Event\FileChangedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileMovedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileRemovedEvent;
use Dontdrinkandroot\GitkiBundle\Repository\ElasticsearchRepositoryInterface;
use Dontdrinkandroot\GitkiBundle\Service\Git\GitServiceInterface;
use Dontdrinkandroot\Path\FilePath;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
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
     * @var GitServiceInterface
     */
    private $gitRepository;

    public function __construct(GitServiceInterface $gitRepository, ElasticsearchRepositoryInterface $repository)
    {
        $this->gitRepository = $gitRepository;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function search($searchString)
    {
        return $this->repository->search($searchString);
    }

    /**
     * {@inheritdoc}
     */
    public function indexFile(FilePath $filePath)
    {
        if (!isset($this->analyzers[$filePath->getExtension()])) {
            return null;
        }

        $analyzer = $this->analyzers[$filePath->getExtension()];
        $content = $this->gitRepository->getContent($filePath);
        $document = $analyzer->analyze($filePath, $content);

        $this->repository->indexFile($filePath, $document);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFile(FilePath $filePath)
    {
        $this->repository->deleteFile($filePath);
    }

    /**
     * {@inheritdoc}
     */
    public function clearIndex()
    {
        $this->repository->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function onFileChanged(FileChangedEvent $event)
    {
        $this->indexFile($event->getFile());
    }

    /**
     * {@inheritdoc}
     */
    public function onFileRemoved(FileRemovedEvent $event)
    {
        $this->deleteFile($event->getFile());
    }

    /**
     * {@inheritdoc}
     */
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
