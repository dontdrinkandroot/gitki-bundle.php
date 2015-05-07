<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Directory;

use Dontdrinkandroot\GitkiBundle\Model\DirectoryListing;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\Directory;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;

interface DirectoryServiceInterface
{

    /**
     * @param DirectoryPath $directoryPath
     *
     * @return FilePath|null
     */
    public function resolveIndexFile(DirectoryPath $directoryPath);

    /**
     * @param DirectoryPath $relativeDirectoryPath
     *
     * @return DirectoryListing
     */
    public function listDirectory(DirectoryPath $relativeDirectoryPath);

    /**
     * @param DirectoryPath $rootPath
     * @param bool          $includeRoot
     *
     * @return Directory[]
     */
    public function findSubDirectories(DirectoryPath $rootPath, $includeRoot = true);
}
