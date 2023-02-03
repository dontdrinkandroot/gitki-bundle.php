<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Wiki;

use Dontdrinkandroot\GitkiBundle\Event\FileChangedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileMovedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileRemovedEvent;
use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\GitkiBundle\Service\Git\GitServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Lock\LockService;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EventDispatchingWikiService extends WikiService
{
    public function __construct(
        GitServiceInterface $gitRepository,
        LockService $lockService,
        protected EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($gitRepository, $lockService);
    }

    /**
     * {@inheritdoc}
     */
    public function saveFile(GitUserInterface $user, FilePath $relativeFilePath, $content, $commitMessage): void
    {
        parent::saveFile($user, $relativeFilePath, $content, $commitMessage);

        $this->eventDispatcher->dispatch(
            new FileChangedEvent($user, $commitMessage, time(), $relativeFilePath, $content)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function renameFile(
        GitUserInterface $user,
        FilePath $relativeOldFilePath,
        FilePath $relativeNewFilePath,
        $commitMessage
    ): void {
        parent::renameFile($user, $relativeOldFilePath, $relativeNewFilePath, $commitMessage);

        $this->eventDispatcher->dispatch(
            new FileMovedEvent($user, $commitMessage, time(), $relativeNewFilePath, $relativeOldFilePath)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function removeFile(GitUserInterface $user, FilePath $relativeFilePath, $commitMessage): void
    {
        parent::removeFile($user, $relativeFilePath, $commitMessage);

        $this->eventDispatcher->dispatch(
            new FileRemovedEvent($user, $commitMessage, time(), $relativeFilePath)
        );
    }
}
