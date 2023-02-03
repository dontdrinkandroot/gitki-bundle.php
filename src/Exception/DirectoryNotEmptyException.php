<?php

namespace Dontdrinkandroot\GitkiBundle\Exception;

use Dontdrinkandroot\Path\DirectoryPath;
use Exception;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class DirectoryNotEmptyException extends Exception
{
    public function __construct(DirectoryPath $directoryPath)
    {
        parent::__construct($directoryPath->toRelativeString(DIRECTORY_SEPARATOR) . ' is not empty');
    }
}
