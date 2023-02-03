<?php

namespace Dontdrinkandroot\GitkiBundle\Event;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;

class FileChangedEvent extends AbstractFileEvent
{
    final const NAME = 'ddr.gitki.file.changed';

    public function __construct(
        GitUserInterface $user,
        string $commitMessage,
        int $time,
        FilePath $file,
        private readonly string $content
    ) {
        parent::__construct($user, $commitMessage, $time, $file);
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
