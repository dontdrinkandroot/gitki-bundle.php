<?php

namespace Dontdrinkandroot\GitkiBundle\Event;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Contracts\EventDispatcher\Event;

class AbstractFileEvent extends Event
{
    public function __construct(
        private readonly GitUserInterface $user,
        private readonly string $commitMessage,
        private readonly int $time,
        private readonly FilePath $file
    ) {
    }

    /**
     * @return GitUserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getCommitMessage()
    {
        return $this->commitMessage;
    }

    /**
     * @return int
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return FilePath
     */
    public function getFile()
    {
        return $this->file;
    }
}
