<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Wiki;

use Dontdrinkandroot\GitkiBundle\Exception\DirectoryNotEmptyException;
use Dontdrinkandroot\GitkiBundle\Exception\FileExistsException;
use Dontdrinkandroot\GitkiBundle\Model\CommitMetadata;
use Dontdrinkandroot\GitkiBundle\Model\DirectoryListing;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\Directory;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\PageFile;
use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\GitkiBundle\Repository\GitRepositoryInterface;
use Dontdrinkandroot\GitkiBundle\Service\LockService;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\Path;
use GitWrapper\GitException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class WikiService
{

    /**
     * @var GitRepositoryInterface
     */
    protected $gitRepository;

    /**
     * @var LockService
     */
    private $lockService;

    /**
     * @var array
     */
    protected $editableExtensions = [];

    /**
     * @param GitRepositoryInterface $gitRepository
     * @param LockService            $lockService
     */
    public function __construct(
        GitRepositoryInterface $gitRepository,
        LockService $lockService
    ) {
        $this->gitRepository = $gitRepository;
        $this->lockService = $lockService;
    }

    /**
     * @param Path $relativePath
     *
     * @return bool
     */
    public function exists(Path $relativePath)
    {
        return $this->gitRepository->exists($relativePath);
    }

    /**
     * @param GitUserInterface $user
     * @param FilePath         $relativeFilePath
     */
    public function createLock(GitUserInterface $user, FilePath $relativeFilePath)
    {
        $this->lockService->createLock($user, $relativeFilePath);
    }

    /**
     * @param GitUserInterface $user
     * @param FilePath         $relativeFilePath
     *
     * @throws \Exception
     */
    public function removeLock(GitUserInterface $user, FilePath $relativeFilePath)
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
        return $this->gitRepository->getContent($relativeFilePath);
    }

    /**
     * @param GitUserInterface $user
     * @param FilePath         $relativeFilePath
     * @param string           $content
     * @param string           $commitMessage
     *
     * @return \Dontdrinkandroot\GitkiBundle\Model\Document\ParsedMarkdownDocument
     *
     * @throws \Exception
     */
    public function saveFile(GitUserInterface $user, FilePath $relativeFilePath, $content, $commitMessage)
    {
        $this->assertCommitMessageExists($commitMessage);
        $this->lockService->assertUserHasLock($user, $relativeFilePath);
        $this->gitRepository->putContent($relativeFilePath, $content);
        $this->gitRepository->addAndCommit($user, $commitMessage, $relativeFilePath);
    }

    /**
     * @param GitUserInterface $user
     * @param FilePath         $relativeFilePath
     *
     * @return int
     */
    public function holdLock(GitUserInterface $user, FilePath $relativeFilePath)
    {
        return $this->lockService->holdLockForUser($user, $relativeFilePath);
    }

    /**
     * @param GitUserInterface $user
     * @param FilePath         $relativeFilePath
     * @param string           $commitMessage
     *
     * @throws \Exception
     */
    public function deleteFile(GitUserInterface $user, FilePath $relativeFilePath, $commitMessage)
    {
        $this->assertCommitMessageExists($commitMessage);
        $this->createLock($user, $relativeFilePath);
        $this->gitRepository->removeAndCommit($user, $commitMessage, $relativeFilePath);
        $this->removeLock($user, $relativeFilePath);
    }

    /**
     * @param DirectoryPath $relativeDirectoryPath
     *
     * @throws DirectoryNotEmptyException
     */
    public function deleteDirectory(DirectoryPath $relativeDirectoryPath)
    {
        $this->assertDirectoryIsEmpty($relativeDirectoryPath);
        $this->gitRepository->removeDirectory($relativeDirectoryPath);
    }

    /**
     * @param GitUserInterface $user
     * @param FilePath         $relativeOldFilePath
     * @param FilePath         $relativeNewFilePath
     * @param string           $commitMessage
     *
     * @throws FileExistsException
     * @throws \Exception
     */
    public function renameFile(
        GitUserInterface $user,
        FilePath $relativeOldFilePath,
        FilePath $relativeNewFilePath,
        $commitMessage
    ) {
        $this->assertFileDoesNotExist($relativeNewFilePath);

        $this->assertCommitMessageExists($commitMessage);

        $this->lockService->assertUserHasLock($user, $relativeOldFilePath);
        $this->createLock($user, $relativeNewFilePath);

        $this->gitRepository->moveAndCommit(
            $user,
            $commitMessage,
            $relativeOldFilePath,
            $relativeNewFilePath
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
    ) {
        $relativeDirectoryPath = $relativeFilePath->getParentPath();

        $this->assertFileDoesNotExist($relativeFilePath);

        if (!$this->gitRepository->exists($relativeDirectoryPath)) {
            $this->gitRepository->mkdir($relativeDirectoryPath);
        }

        $this->createLock($user, $relativeFilePath);
        $uploadedFile->move(
            $this->gitRepository->getAbsolutePath($relativeDirectoryPath),
            $relativeFilePath->getName()
        );

        $this->gitRepository->addAndCommit($user, $commitMessage, $relativeFilePath);

        $this->removeLock($user, $relativeFilePath);
    }

    /**
     * @return FilePath[]
     */
    public function findAllFiles()
    {
        $finder = new Finder();
        $finder->in($this->gitRepository->getRepositoryPath()->toAbsoluteString(DIRECTORY_SEPARATOR));

        $filePaths = [];

        foreach ($finder->files() as $file) {
            /** @var SplFileInfo $file */
            $filePaths[] = FilePath::parse('/' . $file->getRelativePathname());
        }

        return $filePaths;
    }

    /**
     * @param DirectoryPath $relativeDirectoryPath
     *
     * @return DirectoryListing
     */
    public function listDirectory(DirectoryPath $relativeDirectoryPath)
    {
        $repositoryPath = $this->gitRepository->getRepositoryPath();
        $absoluteDirectoryPath = $this->gitRepository->getAbsolutePath($relativeDirectoryPath);

        /* @var PageFile[] $pages */
        $pages = array();
        /* @var Directory[] $subDirectories */
        $subDirectories = array();
        /* @var \Dontdrinkandroot\GitkiBundle\Model\FileInfo\File[] $otherFiles */
        $otherFiles = array();

        $finder = new Finder();
        $finder->in($absoluteDirectoryPath->toAbsoluteString(DIRECTORY_SEPARATOR));
        $finder->depth(0);
        foreach ($finder->files() as $file) {
            /* @var \Symfony\Component\Finder\SplFileInfo $file */
            if ($file->getExtension() == "md") {
                $pages[] = $this->createPageFile($repositoryPath, $relativeDirectoryPath, $file);
            } else {
                if ($file->getExtension() != 'lock') {
                    $otherFile = new \Dontdrinkandroot\GitkiBundle\Model\FileInfo\File(
                        $repositoryPath->toAbsoluteString(DIRECTORY_SEPARATOR),
                        $relativeDirectoryPath->toRelativeString(DIRECTORY_SEPARATOR),
                        $file->getRelativePathName()
                    );
                    $otherFiles[] = $otherFile;
                }
            }
        }

        $finder = new Finder();
        $finder->in($absoluteDirectoryPath->toAbsoluteString(DIRECTORY_SEPARATOR));
        $finder->depth(0);
        $finder->ignoreDotFiles(true);
        foreach ($finder->directories() as $directory) {
            /* @var \Symfony\Component\Finder\SplFileInfo $directory */
            $subDirectory = new Directory(
                $repositoryPath->toAbsoluteString(DIRECTORY_SEPARATOR),
                $relativeDirectoryPath->toRelativeString(DIRECTORY_SEPARATOR),
                $directory->getRelativePathName() . DIRECTORY_SEPARATOR
            );
            $subDirectories[] = $subDirectory;
        }

        usort(
            $pages,
            function (PageFile $a, PageFile $b) {
                return strcmp($a->getTitle(), $b->getTitle());
            }
        );
        usort(
            $subDirectories,
            function (Directory $a, Directory $b) {
                return strcmp($a->getFilename(), $b->getFilename());
            }
        );
        usort(
            $otherFiles,
            function (
                \Dontdrinkandroot\GitkiBundle\Model\FileInfo\File $a,
                \Dontdrinkandroot\GitkiBundle\Model\FileInfo\File $b
            ) {
                return strcmp($a->getFilename(), $b->getFilename());
            }
        );

        return new DirectoryListing($relativeDirectoryPath, $pages, $subDirectories, $otherFiles);
    }

    /**
     * @param FilePath $path
     *
     * @return File
     */
    public function getFile(FilePath $path)
    {
        $absolutePath = $this->gitRepository->getAbsolutePath($path);

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
            return $this->gitRepository->getWorkingCopyHistory($maxCount);
        } catch (GitException $e) {
            if ($e->getMessage() === "fatal: bad default revision 'HEAD'\n") {
                /* swallow, history not there yet */
                return [];
            } else {
                throw $e;
            }
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
        return $this->gitRepository->getFileHistory($path, $maxCount);
    }

    /**
     * @param string $extension
     */
    public function registerEditableExtension($extension)
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
    public function createFolder(DirectoryPath $path)
    {
        $this->gitRepository->createFolder($path);
    }

    /**
     * @param DirectoryPath $repositoryPath
     * @param DirectoryPath $directoryPath
     * @param SplFileInfo   $file
     *
     * @return PageFile
     */
    protected function createPageFile(DirectoryPath $repositoryPath, DirectoryPath $directoryPath, SplFileInfo $file)
    {
        $pageFile = new PageFile(
            $repositoryPath->toAbsoluteString(DIRECTORY_SEPARATOR),
            $directoryPath->toRelativeString(DIRECTORY_SEPARATOR),
            $file->getRelativePathName()
        );
        $pageFile->setTitle($pageFile->getRelativePath()->getFileName());

        return $pageFile;
    }

    protected function assertCommitMessageExists($commitMessage)
    {
        if (empty($commitMessage)) {
            throw new \Exception('Commit message must not be empty');
        }
    }

    /**
     * @param DirectoryPath $relativeDirectoryPath
     *
     * @throws DirectoryNotEmptyException
     */
    protected function assertDirectoryIsEmpty(DirectoryPath $relativeDirectoryPath)
    {
        $absoluteDirectoryPath = $this->gitRepository->getAbsolutePath($relativeDirectoryPath);
        $finder = new Finder();
        $finder->in($absoluteDirectoryPath->toAbsoluteString(DIRECTORY_SEPARATOR));
        $numFiles = $finder->files()->count();
        if ($numFiles > 0) {
            throw new DirectoryNotEmptyException($relativeDirectoryPath);
        }
    }

    /**
     * @param FilePath $relativeNewFilePath
     *
     * @throws FileExistsException
     */
    protected function assertFileDoesNotExist(FilePath $relativeNewFilePath)
    {
        if ($this->gitRepository->exists($relativeNewFilePath)) {
            throw new FileExistsException(
                'File ' . $relativeNewFilePath->toRelativeString(DIRECTORY_SEPARATOR) . ' already exists'
            );
        }
    }
}
