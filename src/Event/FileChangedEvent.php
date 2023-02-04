<?php

namespace Dontdrinkandroot\GitkiBundle\Event;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;

class FileChangedEvent extends AbstractFileEvent
{
    public function __construct(
        GitUserInterface $user,
        string $commitMessage,
        int $time,
        FilePath $file,
        public readonly string $content
    ) {
        parent::__construct($user, $commitMessage, $time, $file);
    }
}
