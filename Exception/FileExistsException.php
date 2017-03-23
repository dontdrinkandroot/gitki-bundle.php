<?php


namespace Dontdrinkandroot\GitkiBundle\Exception;

use Dontdrinkandroot\Path\FilePath;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class FileExistsException extends \Exception
{
    public function __construct(FilePath $filePath)
    {
        parent::__construct('File ' . $filePath->toRelativeString() . ' already exists');
    }
}
