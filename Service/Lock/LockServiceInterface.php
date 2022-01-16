<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Lock;

use Dontdrinkandroot\GitkiBundle\Exception\FileLockExpiredException;
use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;

interface LockServiceInterface
{
    /**
     * @param GitUserInterface $user
     * @param FilePath         $path
     */
    public function createLock(GitUserInterface $user, FilePath $path): void;

    /**
     * @param GitUserInterface $user
     * @param FilePath         $path
     */
    public function removeLock(GitUserInterface $user, FilePath $path): void;

    /**
     * @param GitUserInterface $user
     * @param FilePath         $path
     *
     * @throws FileLockExpiredException
     *
     * @return bool
     */
    public function assertUserHasLock(GitUserInterface $user, FilePath $path): bool;

    /**
     * @param GitUserInterface $user
     * @param FilePath         $path
     *
     * @throws FileLockExpiredException
     *
     * @return int
     */
    public function holdLockForUser(GitUserInterface $user, FilePath $path): int;
}
