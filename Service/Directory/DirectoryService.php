<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Directory;

use Dontdrinkandroot\GitkiBundle\Model\DirectoryListing;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\Directory;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\File;
use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemServiceInterface;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Service\AbstractService;

class DirectoryService extends AbstractService implements DirectoryServiceInterface
{
    /**
     * @var FileSystemServiceInterface
     */
    protected $fileSystemService;

    /** @var string[] */
    protected $indexFiles = [];

    /**
     * @param FileSystemServiceInterface $fileSystemService
     */
    public function __construct(
        FileSystemServiceInterface $fileSystemService
    ) {
        $this->fileSystemService = $fileSystemService;
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
            if ($this->fileSystemService->exists($filePath)) {
                return $filePath;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectoryListing(DirectoryPath $relativeDirectoryPath)
    {
        $files = $this->listFiles($relativeDirectoryPath);
        $subDirectories = $this->fileSystemService->listDirectories($relativeDirectoryPath, false, false);

        usort(
            $subDirectories,
            function (Directory $a, Directory $b) {
                return strcmp($a->getFilename(), $b->getFilename());
            }
        );

        usort(
            $files,
            function (File $a, File $b) {
                $titleA = $a->getTitle();
                if (null === $titleA) {
                    $titleA = $a->getFilename();
                }
                $titleB = $b->getTitle();
                if (null === $titleB) {
                    $titleB = $b->getFilename();
                }

                return strcmp($titleA, $titleB);
            }
        );

        return new DirectoryListing($relativeDirectoryPath, $subDirectories, $files);
    }

    /**
     * {@inheritdoc}
     */
    public function listDirectories(DirectoryPath $rootPath, $includeRoot = true, $recursive = false)
    {
        return $this->fileSystemService->listDirectories($rootPath, $includeRoot, $recursive);
    }

    /**
     * {@inheritdoc}
     */
    public function listFiles(DirectoryPath $relativeDirectoryPath, $recursive = false)
    {
        return $this->fileSystemService->listFiles($relativeDirectoryPath, $recursive);
    }
}
