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
    public function createLock(GitUserInterface $user, FilePath $path)
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
    public function removeLock(GitUserInterface $user, FilePath $path)
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
    public function assertUserHasLock(GitUserInterface $user, FilePath $path)
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
    public function holdLockForUser(GitUserInterface $user, FilePath $path)
    {
        $this->assertUserHasLock($user, $path);
        $lockPath = $this->getLockPath($path);

        $this->fileSystemService->touchFile($lockPath);

        return $this->getLockExpiry($lockPath);
    }

    /**
     * @param FilePath $lockPath
     *
     * @return bool
     */
    protected function isLockExpired(FilePath $lockPath)
    {
        $expired = time() > $this->getLockExpiry($lockPath);
        if ($expired) {
            $this->removeLockFile($lockPath);
        }

        return $expired;
    }

    /**
     * @param FilePath $lockPath
     *
     * @return int
     */
    protected function getLockExpiry(FilePath $lockPath)
    {
        $modificationTime = $this->fileSystemService->getModificationTime($lockPath);

        return $modificationTime + (60);
    }

    /**
     * @param FilePath $lockPath
     *
     * @return string
     */
    protected function getLockLogin(FilePath $lockPath)
    {
        return $this->fileSystemService->getContent($lockPath);
    }

    /**
     * @param FilePath $filePath
     *
     * @return FilePath
     */
    protected function getLockPath(FilePath $filePath)
    {
        $name = $filePath->getName();
        $relativeLockPath = $filePath->getParentPath()->appendFile('.' . $name . '.lock');

        return $relativeLockPath;
    }

    /**
     * @param FilePath $lockPath
     */
    protected function removeLockFile(FilePath $lockPath)
    {
        $this->fileSystemService->removeFile($lockPath);
    }

    /**
     * @param GitUserInterface $user
     * @param FilePath         $lockPath
     *
     * @return bool
     * @throws FileLockedException
     */
    protected function assertUnlocked(GitUserInterface $user, FilePath $lockPath)
    {
        if (!$this->fileSystemService->exists($lockPath)) {
            return true;
        }

        if ($this->isLockExpired($lockPath)) {
            return true;
        }

        $lockLogin = $this->getLockLogin($lockPath);
        if ($lockLogin == $user->getGitUserEmail()) {
            return true;
        }

        throw new FileLockedException($lockLogin, $this->getLockExpiry($lockPath));
    }
}
