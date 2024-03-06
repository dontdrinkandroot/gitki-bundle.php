<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Directory;

use Dontdrinkandroot\GitkiBundle\Model\DirectoryListing;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\Directory;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\File;
use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemServiceInterface;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Override;

class DirectoryService implements DirectoryServiceInterface
{
    /** @var list<string> */
    protected array $indexFiles = [];

    public function __construct(protected FileSystemServiceInterface $fileSystemService)
    {
    }

    /** @param list<string> $indexFiles */
    public function setIndexFiles(array $indexFiles): void
    {
        $this->indexFiles = $indexFiles;
    }

    #[Override]
    public function getPrimaryIndexFile(DirectoryPath $directoryPath): ?FilePath
    {
        if (count($this->indexFiles) > 0) {
            return $directoryPath->appendFile($this->indexFiles[0]);
        }

        return null;
    }

    #[Override]
    public function resolveExistingIndexFile(DirectoryPath $directoryPath): ?FilePath
    {
        foreach ($this->indexFiles as $indexFile) {
            $filePath = $directoryPath->appendFile($indexFile);
            if ($this->fileSystemService->exists($filePath)) {
                return $filePath;
            }
        }

        return null;
    }

    #[Override]
    public function getDirectoryListing(DirectoryPath $relativeDirectoryPath): DirectoryListing
    {
        $files = $this->listFiles($relativeDirectoryPath);
        $subDirectories = $this->fileSystemService->listDirectories($relativeDirectoryPath, false, false);

        usort(
            $subDirectories,
            fn(Directory $a, Directory $b): int => strcmp($a->getFilename(), $b->getFilename())
        );

        usort(
            $files,
            function (File $a, File $b): int {
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

    #[Override]
    public function listDirectories(DirectoryPath $rootPath, bool $includeRoot = true, bool $recursive = false): array
    {
        return $this->fileSystemService->listDirectories($rootPath, $includeRoot, $recursive);
    }

    #[Override]
    public function listFiles(DirectoryPath $relativeDirectoryPath, bool $recursive = false): array
    {
        return $this->fileSystemService->listFiles($relativeDirectoryPath, $recursive);
    }
}
