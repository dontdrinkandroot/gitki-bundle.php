<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Directory;

use Dontdrinkandroot\GitkiBundle\Model\DirectoryListing;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\Directory;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\File;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;

interface DirectoryServiceInterface
{
    public function getPrimaryIndexFile(DirectoryPath $directoryPath): ?FilePath;

    public function resolveExistingIndexFile(DirectoryPath $directoryPath): ?FilePath;

    public function getDirectoryListing(DirectoryPath $relativeDirectoryPath): DirectoryListing;

    /**
     * @return Directory[]
     */
    public function listDirectories(DirectoryPath $rootPath, bool $includeRoot = true, bool $recursive = false): array;

    /**
     * @return File[]
     */
    public function listFiles(DirectoryPath $relativeDirectoryPath, bool $recursive = false);
}
