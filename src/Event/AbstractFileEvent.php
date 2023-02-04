<?php

namespace Dontdrinkandroot\GitkiBundle\Event;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Contracts\EventDispatcher\Event;

class AbstractFileEvent extends Event
{
    public function __construct(
        public readonly GitUserInterface $user,
        public readonly string $commitMessage,
        public readonly int $time,
        public readonly FilePath $file
    ) {
    }

    public function getFile(): FilePath
    {
        return $this->file;
    }
}
