<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Directory;

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
}
