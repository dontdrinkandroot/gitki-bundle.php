<?php

namespace Dontdrinkandroot\GitkiBundle\Model;

use Dontdrinkandroot\GitkiBundle\Model\FileInfo\Directory;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\File;
use Dontdrinkandroot\Path\DirectoryPath;

class DirectoryListing
{
    /**
     * @param Directory[] $subdirectories
     * @param File[] $files
     */
    public function __construct(
        private readonly DirectoryPath $path,
        private readonly array $subdirectories,
        private readonly array $files
    ) {
    }

    /**
     * @return DirectoryPath
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return Directory[]
     */
    public function getSubdirectories()
    {
        return $this->subdirectories;
    }

    /**
     * @return File[]
     */
    public function getFiles()
    {
        return $this->files;
    }
}
