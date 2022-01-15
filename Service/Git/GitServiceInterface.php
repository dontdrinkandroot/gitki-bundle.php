<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Git;

use Dontdrinkandroot\GitkiBundle\Model\CommitMetadata;
use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\Path;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
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
     * @param GitUserInterface    $author
     * @param FilePath[]|FilePath $paths
     * @param string              $commitMessage
     */
    public function removeAndCommit(GitUserInterface $author, $paths, $commitMessage);

    /**
     * @param Path $path
     *
     * @return Path
     */
    public function getAbsolutePath(Path $path);

    /**
     * @param GitUserInterface $author
     * @param FilePath         $oldPath
     * @param FilePath         $newPath
     * @param string           $commitMessage
     */
    public function moveAndCommit(GitUserInterface $author, FilePath $oldPath, FilePath $newPath, $commitMessage);

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
     * @param FilePath         $path
     * @param string           $content
     * @param string           $commitMessage
     */
    public function putAndCommitFile($author, FilePath $path, $content, $commitMessage);

    public function addAndCommitUploadedFile(
        GitUserInterface $author,
        FilePath $path,
        UploadedFile $uploadedFile,
        string $commitMessage
    );
}
