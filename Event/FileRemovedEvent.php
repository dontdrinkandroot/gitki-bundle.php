<?php

namespace Dontdrinkandroot\GitkiBundle\Event;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class FileRemovedEvent extends AbstractFileEvent
{
    const NAME = 'ddr.gitki.file.removed';

    public function __construct(GitUserInterface $user, $commitMessage, $time, FilePath $file)
    {
        parent::__construct($user, $commitMessage, $time, $file);
    }
}
