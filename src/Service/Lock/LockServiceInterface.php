<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Lock;

use Dontdrinkandroot\GitkiBundle\Exception\FileLockedException;
use Dontdrinkandroot\GitkiBundle\Exception\FileLockExpiredException;
use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;

interface LockServiceInterface
{
    /**
     * @param GitUserInterface $user
     * @param FilePath $path
     *
     * @throws FileLockedException
     */
    public function createLock(GitUserInterface $user, FilePath $path): void;

    /**
     * @param GitUserInterface $user
     * @param FilePath $path
     */
    public function removeLock(GitUserInterface $user, FilePath $path): void;

    /**
     * @param GitUserInterface $user
     * @param FilePath $path
     *
     * @return bool
     * @throws FileLockExpiredException
     *
     */
    public function assertUserHasLock(GitUserInterface $user, FilePath $path): bool;

    /**
     * @param GitUserInterface $user
     * @param FilePath $path
     *
     * @return int
     * @throws FileLockExpiredException
     *
     */
    public function holdLockForUser(GitUserInterface $user, FilePath $path): int;
}
