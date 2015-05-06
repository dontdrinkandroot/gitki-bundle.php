<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Lock;

use Dontdrinkandroot\GitkiBundle\Exception\FileLockedException;
use Dontdrinkandroot\GitkiBundle\Exception\FileLockExpiredException;
use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemServiceInterface;
use Dontdrinkandroot\Path\FilePath;

class LockService
{

    /**
     * @var FileSystemServiceInterface
     */
    private $fileSystemService;

    /**
     * @param FileSystemServiceInterface $fileSystemService
     */
    public function __construct(FileSystemServiceInterface $fileSystemService)
    {
        $this->fileSystemService = $fileSystemService;
    }

    /**
     * @param GitUserInterface $user
     * @param FilePath         $relativeFilePath
     *
     * @throws FileLockedException
     */
    public function createLock(GitUserInterface $user, FilePath $relativeFilePath)
    {
        $relativeLockPath = $this->getLockPath($relativeFilePath);
        $relativeLockDir = $relativeLockPath->getParentPath();

        $this->assertUnlocked($user, $relativeLockPath);

        if (!$this->fileSystemService->exists($relativeLockDir)) {
            $this->fileSystemService->createDirectory($relativeLockDir);
        }

        if ($this->fileSystemService->exists($relativeLockPath)) {
            $this->fileSystemService->touchFile($relativeLockPath);
        } else {
            $this->fileSystemService->putContent($relativeLockPath, $user->getEmail());
        }
    }

    /**
     * @param GitUserInterface $user
     * @param FilePath         $relativeFilePath
     *
     * @throws \Exception
     */
    public function removeLock(GitUserInterface $user, FilePath $relativeFilePath)
    {
        $relativeLockPath = $this->getLockPath($relativeFilePath);
        if (!$this->fileSystemService->exists($relativeLockPath)) {
            return;
        }

        if ($this->isLockExpired($relativeLockPath)) {
            return;
        }

        $lockLogin = $this->getLockLogin($relativeLockPath);
        if ($lockLogin != $user->getEmail()) {
            throw new \Exception('Cannot remove lock of different user');
        }

        $this->removeLockFile($relativeLockPath);
    }

    /**
     * @param GitUserInterface $user
     * @param FilePath         $relativeFilePath
     *
     * @return bool
     * @throws FileLockExpiredException
     */
    public function assertUserHasLock(GitUserInterface $user, FilePath $relativeFilePath)
    {
        $lockPath = $this->getLockPath($relativeFilePath);
        if ($this->fileSystemService->exists($lockPath) && !$this->isLockExpired($lockPath)) {
            $lockLogin = $this->getLockLogin($lockPath);
            if ($lockLogin == $user->getEmail()) {
                return true;
            }
        }

        throw new FileLockExpiredException();
    }

    /**
     * @param GitUserInterface $user
     * @param FilePath         $relativeFilePath
     *
     * @return int
     * @throws FileLockExpiredException
     */
    public function holdLockForUser(GitUserInterface $user, FilePath $relativeFilePath)
    {
        $this->assertUserHasLock($user, $relativeFilePath);
        $lockPath = $this->getLockPath($relativeFilePath);

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
     * @param FilePath $relativeLockPath
     *
     * @return int
     */
    protected function getLockExpiry(FilePath $relativeLockPath)
    {
        $mTime = $this->fileSystemService->getModificationTime($relativeLockPath);

        return $mTime + (60);
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
     * @param FilePath $relativeFilePath
     *
     * @return FilePath
     */
    protected function getLockPath(FilePath $relativeFilePath)
    {
        $name = $relativeFilePath->getName();
        $relativeLockPath = $relativeFilePath->getParentPath()->appendFile($name . '.lock');

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
     * @param FilePath         $relativeLockPath
     *
     * @return bool
     * @throws FileLockedException
     */
    protected function assertUnlocked(GitUserInterface $user, FilePath $relativeLockPath)
    {
        if (!$this->fileSystemService->exists($relativeLockPath)) {
            return true;
        }

        if ($this->isLockExpired($relativeLockPath)) {
            return true;
        }

        $lockLogin = $this->getLockLogin($relativeLockPath);
        if ($lockLogin == $user->getEmail()) {
            return true;
        }

        throw new FileLockedException($lockLogin, $this->getLockExpiry($relativeLockPath));
    }
}
