<?php

namespace Dontdrinkandroot\GitkiBundle\Event;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;

class FileMovedEvent extends AbstractFileEvent
{
    final const NAME = 'ddr.gitki.file.moved';

    public function __construct(
        GitUserInterface $user,
        string $commitMessage,
        int $time,
        FilePath $file,
        private readonly FilePath $previousFile
    ) {
        parent::__construct($user, $commitMessage, $time, $file);
    }

    /**
     * @return FilePath
     */
    public function getPreviousFile()
    {
        return $this->previousFile;
    }
}
