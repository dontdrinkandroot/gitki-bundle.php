<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Directory;

use Dontdrinkandroot\GitkiBundle\Repository\GitRepositoryInterface;
use Dontdrinkandroot\Path\DirectoryPath;

class DirectoryService implements DirectoryServiceInterface
{

    /**
     * @var GitRepositoryInterface
     */
    private $gitRepository;

    private $indexFiles = [];

    /**
     * @param GitRepositoryInterface $gitRepository
     */
    public function __construct(GitRepositoryInterface $gitRepository)
    {
        $this->gitRepository = $gitRepository;
    }

    /**
     * @param string[] $indexFiles
     */
    public function setIndexFiles(array $indexFiles)
    {
        $this->indexFiles = $indexFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveIndexFile(DirectoryPath $directoryPath)
    {
        foreach ($this->indexFiles as $indexFile) {
            $filePath = $directoryPath->appendFile($indexFile);
            if ($this->gitRepository->exists($filePath)) {
                return $filePath;
            }
        }

        return null;
    }
}
