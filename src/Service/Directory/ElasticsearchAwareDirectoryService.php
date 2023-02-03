<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Directory;

use Dontdrinkandroot\GitkiBundle\Repository\ElasticsearchRepositoryInterface;
use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemServiceInterface;
use Dontdrinkandroot\Path\DirectoryPath;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class ElasticsearchAwareDirectoryService extends DirectoryService
{
    /**
     * @var ElasticsearchRepositoryInterface
     */
    private $elasticsearchRepository;

    /**
     * @param FileSystemServiceInterface $fileSystemService
     * @param ElasticsearchRepositoryInterface $elasticsearchRepository
     */
    public function __construct(
        FileSystemServiceInterface $fileSystemService,
        ElasticsearchRepositoryInterface $elasticsearchRepository
    ) {
        parent::__construct($fileSystemService);
        $this->elasticsearchRepository = $elasticsearchRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function listFiles(DirectoryPath $relativeDirectoryPath, $recursive = false)
    {
        $files = $this->fileSystemService->listFiles($relativeDirectoryPath, $recursive);
        foreach ($files as $file) {
            $title = $this->elasticsearchRepository->findTitle($file->getAbsolutePath());
            if (null !== $title) {
                $file->setTitle($title);
            }
        }

        return $files;
    }
}
