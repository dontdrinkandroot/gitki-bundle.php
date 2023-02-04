<?php

namespace Dontdrinkandroot\GitkiBundle\Exception;

use Exception;

class FileLockedException extends Exception
{
    public function __construct(public readonly string $lockedBy, public readonly int $expires)
    {
        parent::__construct('Page is locked by ' . $lockedBy);
    }
}
