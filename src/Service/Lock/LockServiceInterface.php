<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Lock;

use Dontdrinkandroot\GitkiBundle\Exception\FileLockedException;
use Dontdrinkandroot\GitkiBundle\Exception\FileLockExpiredException;
use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;

interface LockServiceInterface
{
    /**
     * @throws FileLockedException
     */
    public function createLock(GitUserInterface $user, FilePath $path): void;

    public function removeLock(GitUserInterface $user, FilePath $path): void;

    /**
     * @throws FileLockExpiredException
     */
    public function assertUserHasLock(GitUserInterface $user, FilePath $path): bool;

    /**
     * @throws FileLockExpiredException
     */
    public function holdLockForUser(GitUserInterface $user, FilePath $path): int;
}
