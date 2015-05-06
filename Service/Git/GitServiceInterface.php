<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Git;

use Dontdrinkandroot\GitkiBundle\Model\CommitMetadata;
use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\Path;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface GitServiceInterface
{

    /**
     * @param Path $path
     *
     * @return bool
     */
    public function exists(Path $path);

    /**
     * @param DirectoryPath $path
     */
    public function createDirectory(DirectoryPath $path);

    /**
     * @param FilePath $path
     *
     * @return string
     */
    public function getContent(FilePath $path);

    /**
     * @param GitUserInterface $author
     * @param string              $commitMessage
     * @param FilePath[]|FilePath $paths
     */
    public function removeAndCommit(GitUserInterface $author, $commitMessage, $paths);

    /**
     * @param Path $path
     *
     * @return Path
     */
    public function getAbsolutePath(Path $path);

    /**
     * @param GitUserInterface $author
     * @param string           $commitMessage
     * @param FilePath         $oldPath
     * @param FilePath         $newPath
     */
    public function moveAndCommit(GitUserInterface $author, $commitMessage, FilePath $oldPath, FilePath $newPath);

    /**
     * @return DirectoryPath
     */
    public function getRepositoryPath();

    /**
     * @param int|null $maxCount
     *
     * @return CommitMetadata[]
     */
    public function getWorkingCopyHistory($maxCount);

    /**
     * @param FilePath $path
     * @param int|null $maxCount
     *
     * @return CommitMetadata[]
     */
    public function getFileHistory(FilePath $path, $maxCount);

    /**
     * @param DirectoryPath $path
     */
    public function removeDirectory(DirectoryPath $path);

    /**
     * @param GitUserInterface $author
     * @param string           $commitMessage
     * @param FilePath         $path
     * @param string           $content
     */
    public function putAndCommitFile($author, $commitMessage, FilePath $path, $content);

    /**
     * @param GitUserInterface $author
     * @param string           $commitMessage
     * @param FilePath         $path
     * @param UploadedFile     $uploadedFile
     *
     * @return mixed
     */
    public function addAndCommitUploadedFile($author, $commitMessage, FilePath $path, UploadedFile $uploadedFile);
}
