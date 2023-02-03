<?php

namespace Dontdrinkandroot\GitkiBundle\Exception;

use Dontdrinkandroot\Path\FilePath;
use Exception;

class FileExistsException extends Exception
{
    public function __construct(FilePath $filePath)
    {
        parent::__construct('File ' . $filePath->toRelativeString() . ' already exists');
    }
}
