<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Wiki;

use Dontdrinkandroot\Common\Asserted;
use Dontdrinkandroot\GitkiBundle\Exception\ComitMessageMissingException;
use Dontdrinkandroot\GitkiBundle\Exception\DirectoryNotEmptyException;
use Dontdrinkandroot\GitkiBundle\Exception\FileExistsException;
use Dontdrinkandroot\GitkiBundle\Exception\FileLockedException;
use Dontdrinkandroot\GitkiBundle\Model\CommitMetadata;
use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\GitkiBundle\Service\Git\GitServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Lock\LockServiceInterface;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\Path;
use Dontdrinkandroot\Path\RootDirectoryPath;
use Exception;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symplify\GitWrapper\Exception\GitException;

class WikiService
{
    /**
     * @var array
     */
    protected $editableExtensions = [];

    public function __construct(
        protected GitServiceInterface $gitService,
        private readonly LockServiceInterface $lockService
    ) {
    }

    public function exists(Path $relativePath): bool
    {
        return $this->gitService->exists($relativePath);
    }

    /**
     * @throws FileLockedException
     */
    public function createLock(GitUserInterface $user, FilePath $relativeFilePath): void
    {
        $this->lockService->createLock($user, $relativeFilePath);
    }

    /**
     * @throws Exception
     */
    public function removeLock(GitUserInterface $user, FilePath $relativeFilePath): void
    {
        $this->lockService->removeLock($user, $relativeFilePath);
    }

    public function getContent(FilePath $relativeFilePath): string
    {
        return $this->gitService->getContent($relativeFilePath);
    }

    /**
     * @throws Exception
     */
    public function saveFile(
        GitUserInterface $user,
        FilePath $relativeFilePath,
        string $content,
        string $commitMessage
    ): void {
        $this->assertCommitMessageExists($commitMessage);
        $this->lockService->assertUserHasLock($user, $relativeFilePath);
        $this->gitService->putAndCommitFile($user, $relativeFilePath, $content, $commitMessage);
    }

    public function holdLock(GitUserInterface $user, FilePath $relativeFilePath): int
    {
        return $this->lockService->holdLockForUser($user, $relativeFilePath);
    }

    /**
     * @throws Exception
     */
    public function removeFile(GitUserInterface $user, FilePath $relativeFilePath, string $commitMessage): void
    {
        $this->assertCommitMessageExists($commitMessage);
        $this->createLock($user, $relativeFilePath);
        $this->gitService->removeAndCommit($user, $relativeFilePath, $commitMessage);
        $this->removeLock($user, $relativeFilePath);
    }

    /**
     * @throws DirectoryNotEmptyException
     */
    public function removeDirectory(DirectoryPath $relativeDirectoryPath): void
    {
        $this->gitService->removeDirectory($relativeDirectoryPath);
    }

    public function removeDirectoryRecursively(
        GitUserInterface $user,
        DirectoryPath $relativeDirectoryPath,
        string $commitMessage
    ): void {
        $files = $this->findAllFiles($relativeDirectoryPath);

        /* No files contained, just delete */
        if (0 === count($files)) {
            $this->removeDirectory($relativeDirectoryPath);

            return;
        }

        foreach ($files as $file) {
            $this->createLock($user, $file);
        }

        $this->gitService->removeAndCommit($user, $files, $commitMessage);

        foreach ($files as $file) {
            $this->removeLock($user, $file);
        }

        $this->removeDirectory($relativeDirectoryPath);
    }

    /**
     * @throws FileExistsException
     * @throws Exception
     */
    public function renameFile(
        GitUserInterface $user,
        FilePath $relativeOldFilePath,
        FilePath $relativeNewFilePath,
        string $commitMessage
    ): void {
        $this->assertFileDoesNotExist($relativeNewFilePath);

        $this->assertCommitMessageExists($commitMessage);

        $this->lockService->assertUserHasLock($user, $relativeOldFilePath);
        $this->createLock($user, $relativeNewFilePath);

        $this->gitService->moveAndCommit(
            $user,
            $relativeOldFilePath,
            $relativeNewFilePath,
            $commitMessage
        );

        $this->removeLock($user, $relativeOldFilePath);
        $this->removeLock($user, $relativeNewFilePath);
    }

    /**
     * @throws FileExistsException
     */
    public function addFile(
        GitUserInterface $user,
        FilePath $relativeFilePath,
        UploadedFile $uploadedFile,
        string $commitMessage
    ): void {
        $relativeDirectoryPath = $relativeFilePath->getParent();

        $this->assertFileDoesNotExist($relativeFilePath);

        if (!$this->gitService->exists($relativeDirectoryPath)) {
            $this->gitService->createDirectory($relativeDirectoryPath);
        }

        $this->createLock($user, $relativeFilePath);
        $this->gitService->addAndCommitUploadedFile($user, $relativeFilePath, $uploadedFile, $commitMessage);
        $this->removeLock($user, $relativeFilePath);
    }

    /**
     * @return list<FilePath>
     */
    public function findAllFiles(?DirectoryPath $path = null): array
    {
        if (null === $path) {
            $path = new RootDirectoryPath();
        }

        $finder = new Finder();
        $searchPath = $path->prepend($this->gitService->getRepositoryPath());

        $finder->in($searchPath->toAbsoluteString(DIRECTORY_SEPARATOR));

        $filePaths = [];

        foreach ($finder->files() as $file) {
            $filePaths[] = Asserted::instanceOf(
                FilePath::parse('/' . $file->getRelativePathname())->prepend($path),
                FilePath::class
            );
        }

        return $filePaths;
    }

    public function getFile(FilePath $path): File
    {
        $absolutePath = $this->gitService->getAbsolutePath($path);

        return new File($absolutePath->toAbsoluteString(DIRECTORY_SEPARATOR));
    }

    /**
     * @return CommitMetadata[]
     * @throws GitException
     */
    public function getHistory(?int $maxCount)
    {
        try {
            return $this->gitService->getWorkingCopyHistory($maxCount);
        } catch (GitException $e) {
            if ($e->getMessage() === "fatal: bad default revision 'HEAD'\n") {
                /* swallow, history not there yet */
                return [];
            }

            throw $e;
        }
    }

    /**
     * @return CommitMetadata[]
     */
    public function getFileHistory(FilePath $path, ?int $maxCount = null): array
    {
        return $this->gitService->getFileHistory($path, $maxCount);
    }

    public function registerEditableExtension(string $extension): void
    {
        $this->editableExtensions[$extension] = true;
    }

    /**
     * @return string[]
     */
    public function getEditableExtensions(): array
    {
        return array_keys($this->editableExtensions);
    }

    public function createFolder(DirectoryPath $path): void
    {
        $this->gitService->createDirectory($path);
    }

    protected function assertCommitMessageExists(string $commitMessage): void
    {
        if (empty($commitMessage)) {
            throw new ComitMessageMissingException();
        }
    }

    /**
     * @throws FileExistsException
     */
    protected function assertFileDoesNotExist(FilePath $relativeNewFilePath): void
    {
        if ($this->gitService->exists($relativeNewFilePath)) {
            throw new FileExistsException($relativeNewFilePath);
        }
    }
}
