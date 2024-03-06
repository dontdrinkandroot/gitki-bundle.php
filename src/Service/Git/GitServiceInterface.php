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
    public function exists(Path $path): bool;

    public function createDirectory(DirectoryPath $path): void;

    public function getContent(FilePath $path): string;

    /**
     * @param list<FilePath>|FilePath $paths
     */
    public function removeAndCommit(GitUserInterface $author, FilePath|array $paths, string $commitMessage): void;

    public function getAbsolutePath(Path $path): Path;

    public function moveAndCommit(
        GitUserInterface $author,
        FilePath $oldPath,
        FilePath $newPath,
        string $commitMessage
    ): void;

    public function getRepositoryPath(): DirectoryPath;

    /** @return list<CommitMetadata> */
    public function getWorkingCopyHistory(?int $maxCount = null): array;

    /** @return list<CommitMetadata> */
    public function getFileHistory(FilePath $path, ?int $maxCount = null): array;

    public function removeDirectory(DirectoryPath $path): void;

    public function putAndCommitFile(
        GitUserInterface $author,
        FilePath $path,
        string $content,
        string $commitMessage
    ): void;

    public function addAndCommitUploadedFile(
        GitUserInterface $author,
        FilePath $path,
        UploadedFile $uploadedFile,
        string $commitMessage
    ): void;
}
