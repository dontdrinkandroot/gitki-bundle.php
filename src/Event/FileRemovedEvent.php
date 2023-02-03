<?php

namespace Dontdrinkandroot\GitkiBundle\Event;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;

class FileRemovedEvent extends AbstractFileEvent
{
    final const NAME = 'ddr.gitki.file.removed';

    public function __construct(GitUserInterface $user, string $commitMessage, int $time, FilePath $file)
    {
        parent::__construct($user, $commitMessage, $time, $file);
    }
}
