<?php

namespace Dontdrinkandroot\GitkiBundle\Exception;

use Exception;

class FileLockedException extends Exception
{
    private readonly string $lockedBy;

    public function __construct(string $lockedBy, private readonly int $expires)
    {
        parent::__construct('Page is locked by ' . $lockedBy);
        $this->lockedBy = $lockedBy;
    }

    /**
     * @return int
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @return string
     */
    public function getLockedBy()
    {
        return $this->lockedBy;
    }
}
