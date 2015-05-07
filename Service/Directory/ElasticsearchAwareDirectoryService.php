<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Directory;

use Dontdrinkandroot\GitkiBundle\Model\FileInfo\File;
use Dontdrinkandroot\GitkiBundle\Repository\ElasticsearchRepositoryInterface;
use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemServiceInterface;
use Dontdrinkandroot\Path\DirectoryPath;

class ElasticsearchAwareDirectoryService extends DirectoryService
{

    /**
     * @var ElasticsearchRepositoryInterface
     */
    private $elasticsearchRepository;

    /**
     * @param FileSystemServiceInterface       $fileSystemService
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
     * @param DirectoryPath $relativeDirectoryPath
     *
     * @return File[]
     */
    protected function listFiles(DirectoryPath $relativeDirectoryPath)
    {
        $files = $this->fileSystemService->listFiles($relativeDirectoryPath, false);
        foreach ($files as $file) {
            $title = $this->elasticsearchRepository->findTitle($file->getAbsolutePath());
            if (null !== $title) {
                $file->setTitle($title);
            }
        }

        return $files;
    }
}
