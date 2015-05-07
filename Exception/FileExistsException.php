<?php


namespace Dontdrinkandroot\GitkiBundle\Exception;

use Dontdrinkandroot\Path\FilePath;

class FileExistsException extends \Exception
{

    public function __construct(FilePath $filePath)
    {
        parent::__construct('File ' . $filePath->toRelativeString() . ' already exists');
    }
}
