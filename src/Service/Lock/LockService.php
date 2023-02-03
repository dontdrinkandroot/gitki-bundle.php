<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Lock;

use Dontdrinkandroot\GitkiBundle\Exception\FileLockedException;
use Dontdrinkandroot\GitkiBundle\Exception\FileLockExpiredException;
use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemServiceInterface;
use Dontdrinkandroot\Path\FilePath;
use Exception;

class LockService implements LockServiceInterface
{
    public function __construct(private FileSystemServiceInterface $fileSystemService)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createLock(GitUserInterface $user, FilePath $path): void
    {
        $lockPath = $this->getLockPath($path);
        $relativeLockDir = $lockPath->getParentPath();

        $this->assertUnlocked($user, $lockPath);

        if (!$this->fileSystemService->exists($relativeLockDir)) {
            $this->fileSystemService->createDirectory($relativeLockDir);
        }

        if ($this->fileSystemService->exists($lockPath)) {
            $this->fileSystemService->touchFile($lockPath);
        } else {
            $this->fileSystemService->putContent($lockPath, $user->getGitUserEmail());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeLock(GitUserInterface $user, FilePath $path): void
    {
        $lockPath = $this->getLockPath($path);
        if (!$this->fileSystemService->exists($lockPath)) {
            return;
        }

        if ($this->isLockExpired($lockPath)) {
            return;
        }

        $lockLogin = $this->getLockLogin($lockPath);
        if ($lockLogin != $user->getGitUserEmail()) {
            throw new Exception('Cannot remove lock of different user');
        }

        $this->removeLockFile($lockPath);
    }

    /**
     * {@inheritdoc}
     */
    public function assertUserHasLock(GitUserInterface $user, FilePath $path): bool
    {
        $lockPath = $this->getLockPath($path);
        if ($this->fileSystemService->exists($lockPath) && !$this->isLockExpired($lockPath)) {
            $lockLogin = $this->getLockLogin($lockPath);
            if ($lockLogin == $user->getGitUserEmail()) {
                return true;
            }

            throw new FileLockedException($user->getGitUserEmail(), $this->getLockExpiry($lockPath));
        }

        throw new FileLockExpiredException();
    }

    /**
     * {@inheritdoc}
     */
    public function holdLockForUser(GitUserInterface $user, FilePath $path): int
    {
        $this->assertUserHasLock($user, $path);
        $lockPath = $this->getLockPath($path);

        $this->fileSystemService->touchFile($lockPath);

        return $this->getLockExpiry($lockPath);
    }

    protected function isLockExpired(FilePath $lockPath): bool
    {
        $expired = time() > $this->getLockExpiry($lockPath);
        if ($expired) {
            $this->removeLockFile($lockPath);
        }

        return $expired;
    }

    protected function getLockExpiry(FilePath $lockPath): int
    {
        $modificationTime = $this->fileSystemService->getModificationTime($lockPath);

        return $modificationTime + (60);
    }

    protected function getLockLogin(FilePath $lockPath): string
    {
        return $this->fileSystemService->getContent($lockPath);
    }

    protected function getLockPath(FilePath $filePath): FilePath
    {
        $name = $filePath->getName();
        $relativeLockPath = $filePath->getParentPath()->appendFile('.' . $name . '.lock');

        return $relativeLockPath;
    }

    protected function removeLockFile(FilePath $lockPath): void
    {
        $this->fileSystemService->removeFile($lockPath);
    }

    /**
     * @param GitUserInterface $user
     * @param FilePath $lockPath
     *
     * @return bool
     * @throws FileLockedException
     */
    protected function assertUnlocked(GitUserInterface $user, FilePath $lockPath): bool
    {
        if (!$this->fileSystemService->exists($lockPath)) {
            return true;
        }

        if ($this->isLockExpired($lockPath)) {
            return true;
        }

        $lockLogin = $this->getLockLogin($lockPath);
        if ($lockLogin === $user->getGitUserEmail()) {
            return true;
        }

        throw new FileLockedException($lockLogin, $this->getLockExpiry($lockPath));
    }
}
