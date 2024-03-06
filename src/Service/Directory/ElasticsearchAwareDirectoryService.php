<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Directory;

use Dontdrinkandroot\GitkiBundle\Repository\ElasticsearchRepositoryInterface;
use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemServiceInterface;
use Dontdrinkandroot\Path\DirectoryPath;
use Override;

class ElasticsearchAwareDirectoryService extends DirectoryService
{
    /**
     * @param FileSystemServiceInterface $fileSystemService
     * @param ElasticsearchRepositoryInterface $elasticsearchRepository
     */
    public function __construct(
        FileSystemServiceInterface $fileSystemService,
        private readonly ElasticsearchRepositoryInterface $elasticsearchRepository
    ) {
        parent::__construct($fileSystemService);
    }

    #[Override]
    public function listFiles(DirectoryPath $relativeDirectoryPath, bool $recursive = false): array
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
