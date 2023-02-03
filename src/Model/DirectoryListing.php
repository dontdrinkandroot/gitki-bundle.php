<?php

namespace Dontdrinkandroot\GitkiBundle\Model;

use Dontdrinkandroot\GitkiBundle\Model\FileInfo\Directory;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\File;
use Dontdrinkandroot\Path\DirectoryPath;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class DirectoryListing
{
    /**
     * @var DirectoryPath
     */
    private $path;

    /**
     * @var Directory[]
     */
    private $subdirectories;

    /**
     * @var File[]
     */
    private $files;

    /**
     * @param DirectoryPath $path
     * @param Directory[] $subdirectories
     * @param File[] $files
     */
    public function __construct($path, $subdirectories, $files)
    {
        $this->path = $path;
        $this->subdirectories = $subdirectories;
        $this->files = $files;
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
