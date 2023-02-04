<?php

namespace Dontdrinkandroot\GitkiBundle\Model;

use Dontdrinkandroot\GitkiBundle\Model\FileInfo\Directory;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\File;
use Dontdrinkandroot\Path\DirectoryPath;

class DirectoryListing
{
    /**
     * @param Directory[] $subDirectories
     * @param File[] $files
     */
    public function __construct(
        public readonly DirectoryPath $path,
        public readonly array $subDirectories,
        public readonly array $files
    ) {
    }
}
