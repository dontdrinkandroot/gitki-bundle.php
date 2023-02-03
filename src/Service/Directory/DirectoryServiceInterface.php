<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Directory;

use Dontdrinkandroot\GitkiBundle\Model\DirectoryListing;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\Directory;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\File;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
interface DirectoryServiceInterface
{
    public function getPrimaryIndexFile(DirectoryPath $directoryPath): ?FilePath;

    /**
     * @param DirectoryPath $directoryPath
     *
     * @return FilePath|null
     */
    public function resolveExistingIndexFile(DirectoryPath $directoryPath);

    /**
     * @param DirectoryPath $relativeDirectoryPath
     *
     * @return DirectoryListing
     */
    public function getDirectoryListing(DirectoryPath $relativeDirectoryPath);

    /**
     * @param DirectoryPath $rootPath
     * @param bool $includeRoot
     * @param bool $recursive
     *
     * @return Directory[]
     */
    public function listDirectories(DirectoryPath $rootPath, $includeRoot = true, $recursive = false);

    /**
     * @param DirectoryPath $relativeDirectoryPath
     * @param bool $recursive
     *
     * @return File[]
     */
    public function listFiles(DirectoryPath $relativeDirectoryPath, $recursive = false);
}
