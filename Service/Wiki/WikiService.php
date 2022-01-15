<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Wiki;

use Dontdrinkandroot\Common\Asserted;
use Dontdrinkandroot\GitkiBundle\Exception\ComitMessageMissingException;
use Dontdrinkandroot\GitkiBundle\Exception\DirectoryNotEmptyException;
use Dontdrinkandroot\GitkiBundle\Exception\FileExistsException;
use Dontdrinkandroot\GitkiBundle\Model\CommitMetadata;
use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\GitkiBundle\Service\Git\GitServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Lock\LockServiceInterface;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\Path;
use Exception;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symplify\GitWrapper\Exception\GitException;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class WikiService
{
    /**
     * @var GitServiceInterface
     */
    protected $gitService;

    /**
     * @var LockServiceInterface
     */
    private $lockService;

    /**
     * @var array
     */
    protected $editableExtensions = [];

    /**
     * @param GitServiceInterface  $gitService
     * @param LockServiceInterface $lockService
     */
    public function __construct(
        GitServiceInterface $gitService,
        LockServiceInterface $lockService
    ) {
        $this->gitService = $gitService;
        $this->lockService = $lockService;
    }

    /**
     * @param Path $relativePath
     *
     * @return bool
     */
    public function exists(Path $relativePath)
    {
        return $this->gitService->exists($relativePath);
    }

    /**
     * @param GitUserInterface $user
     * @param FilePath         $relativeFilePath
     */
    public function createLock(GitUserInterface $user, FilePath $relativeFilePath): void
    {
        $this->lockService->createLock($user, $relativeFilePath);
    }

    /**
     * @param GitUserInterface $user
     * @param FilePath         $relativeFilePath
     *
     * @throws Exception
     */
    public function removeLock(GitUserInterface $user, FilePath $relativeFilePath): void
    {
        $this->lockService->removeLock($user, $relativeFilePath);
    }

    /**
     * @param FilePath $relativeFilePath
     *
     * @return string
     */
    public function getContent(FilePath $relativeFilePath)
    {
        return $this->gitService->getContent($relativeFilePath);
    }

    /**
     * @param GitUserInterface $user
     * @param FilePath         $relativeFilePath
     * @param string           $content
     * @param string           $commitMessage
     *
     * @throws Exception
     */
    public function saveFile(GitUserInterface $user, FilePath $relativeFilePath, $content, $commitMessage): void
    {
        $this->assertCommitMessageExists($commitMessage);
        $this->lockService->assertUserHasLock($user, $relativeFilePath);
        $this->gitService->putAndCommitFile($user, $relativeFilePath, $content, $commitMessage);
    }

    /**
     * @param GitUserInterface $user
     * @param FilePath         $relativeFilePath
     *
     * @return int
     */
    public function holdLock(GitUserInterface $user, FilePath $relativeFilePath): int
    {
        return $this->lockService->holdLockForUser($user, $relativeFilePath);
    }

    /**
     * @param GitUserInterface $user
     * @param FilePath         $relativeFilePath
     * @param string           $commitMessage
     *
     * @throws Exception
     */
    public function removeFile(GitUserInterface $user, FilePath $relativeFilePath, $commitMessage): void
    {
        $this->assertCommitMessageExists($commitMessage);
        $this->createLock($user, $relativeFilePath);
        $this->gitService->removeAndCommit($user, $relativeFilePath, $commitMessage);
        $this->removeLock($user, $relativeFilePath);
    }

    /**
     * @param DirectoryPath $relativeDirectoryPath
     *
     * @throws DirectoryNotEmptyException
     *
     * @deprecated Use removeDirectory instead
     */
    public function deleteDirectory(DirectoryPath $relativeDirectoryPath): void
    {
        $this->removeDirectory($relativeDirectoryPath);
    }

    /**
     * @param DirectoryPath $relativeDirectoryPath
     *
     * @throws DirectoryNotEmptyException
     */
    public function removeDirectory(DirectoryPath $relativeDirectoryPath): void
    {
        $this->gitService->removeDirectory($relativeDirectoryPath);
    }

    /**
     * @param GitUserInterface $user
     * @param DirectoryPath    $relativeDirectoryPath
     * @param string           $commitMessage
     */
    public function removeDirectoryRecursively(
        GitUserInterface $user,
        DirectoryPath $relativeDirectoryPath,
        $commitMessage
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
     * @param GitUserInterface $user
     * @param FilePath         $relativeOldFilePath
     * @param FilePath         $relativeNewFilePath
     * @param string           $commitMessage
     *
     * @throws FileExistsException
     * @throws Exception
     */
    public function renameFile(
        GitUserInterface $user,
        FilePath $relativeOldFilePath,
        FilePath $relativeNewFilePath,
        $commitMessage
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
     * @param GitUserInterface $user
     * @param FilePath         $relativeFilePath
     * @param UploadedFile     $uploadedFile
     * @param string           $commitMessage
     *
     * @throws FileExistsException
     */
    public function addFile(
        GitUserInterface $user,
        FilePath $relativeFilePath,
        UploadedFile $uploadedFile,
        $commitMessage
    ): void {
        $relativeDirectoryPath = $relativeFilePath->getParentPath();

        $this->assertFileDoesNotExist($relativeFilePath);

        if (!$this->gitService->exists($relativeDirectoryPath)) {
            $this->gitService->createDirectory($relativeDirectoryPath);
        }

        $this->createLock($user, $relativeFilePath);
        $this->gitService->addAndCommitUploadedFile($user, $relativeFilePath, $uploadedFile, $commitMessage);
        $this->removeLock($user, $relativeFilePath);
    }

    /**
     * @param DirectoryPath|null $path
     *
     * @return list<FilePath>
     */
    public function findAllFiles(DirectoryPath $path = null): array
    {
        if (null === $path) {
            $path = new DirectoryPath();
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

    /**
     * @param FilePath $path
     *
     * @return File
     */
    public function getFile(FilePath $path)
    {
        $absolutePath = $this->gitService->getAbsolutePath($path);

        return new File($absolutePath->toAbsoluteString(DIRECTORY_SEPARATOR));
    }

    /**
     * @param int $maxCount
     *
     * @return CommitMetadata[]
     *
     * @throws GitException
     */
    public function getHistory($maxCount)
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
     * @param FilePath $path
     * @param int|null $maxCount
     *
     * @return CommitMetadata[]
     */
    public function getFileHistory(FilePath $path, $maxCount = null)
    {
        return $this->gitService->getFileHistory($path, $maxCount);
    }

    /**
     * @param string $extension
     */
    public function registerEditableExtension($extension): void
    {
        $this->editableExtensions[$extension] = true;
    }

    /**
     * @return string[]
     */
    public function getEditableExtensions()
    {
        return array_keys($this->editableExtensions);
    }

    /**
     * @param DirectoryPath $path
     */
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
     * @param FilePath $relativeNewFilePath
     *
     * @throws FileExistsException
     */
    protected function assertFileDoesNotExist(FilePath $relativeNewFilePath): void
    {
        if ($this->gitService->exists($relativeNewFilePath)) {
            throw new FileExistsException($relativeNewFilePath);
        }
    }
}
