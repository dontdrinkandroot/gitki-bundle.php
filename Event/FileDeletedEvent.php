<?php

namespace Dontdrinkandroot\GitkiBundle\Event;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;

class FileDeletedEvent extends AbstractFileEvent
{

    const NAME = 'ddr.gitki.file.deleted';

    public function __construct(GitUserInterface $user, $commitMessage, $time, FilePath $file)
    {
        parent::__construct($user, $commitMessage, $time, $file);
    }
}
