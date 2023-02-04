<?php

namespace Dontdrinkandroot\GitkiBundle\Event;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;

class FileMovedEvent extends AbstractFileEvent
{
    public function __construct(
        GitUserInterface $user,
        string $commitMessage,
        int $time,
        FilePath $file,
        public readonly FilePath $previousFile
    ) {
        parent::__construct($user, $commitMessage, $time, $file);
    }
}
