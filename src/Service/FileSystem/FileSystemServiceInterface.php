<?php

namespace Dontdrinkandroot\GitkiBundle\Service\FileSystem;

use Dontdrinkandroot\GitkiBundle\Model\FileInfo\Directory;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\File;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\Path;

interface FileSystemServiceInterface
{
    public function getBasePath(): DirectoryPath;

    public function exists(Path $path): bool;

    public function createDirectory(DirectoryPath $path): void;

    public function touchFile(FilePath $path): void;

    public function putContent(FilePath $path, string $content): void;

    public function getModificationTime(Path $path): int;

    public function getContent(FilePath $path): string;

    public function removeFile(FilePath $path): void;

    public function removeDirectory(DirectoryPath $path, bool $ignoreEmpty = false): void;

    public function getAbsolutePath(Path $path): Path;

    /** @return list<Directory> */
    public function listDirectories(DirectoryPath $root, bool $includeRoot = false, bool $recursive = true): array;

    /** @return list<File> */
    public function listFiles(DirectoryPath $root, bool $recursive = true): array;
}
