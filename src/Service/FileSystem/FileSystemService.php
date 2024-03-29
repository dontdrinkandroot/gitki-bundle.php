<?php

namespace Dontdrinkandroot\GitkiBundle\Service\FileSystem;

use Dontdrinkandroot\GitkiBundle\Exception\DirectoryNotEmptyException;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\Directory;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\File;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\Path;
use Exception;
use Override;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Performs file system operations relative to a base path.
 *
 * All paths passed to the functions have to be relative to the base path.
 */
class FileSystemService implements FileSystemServiceInterface
{
    protected DirectoryPath $basePath;

    protected Filesystem $fileSystem;

    /**
     * @throws Exception
     */
    public function __construct(string $basePath)
    {
        $pathString = $basePath;

        if (!str_starts_with($pathString, '/')) {
            throw new RuntimeException('Base Path must be absolute');
        }

        if (!str_ends_with($pathString, '/')) {
            $pathString .= '/';
        }

        $this->basePath = DirectoryPath::parse($pathString);
        $this->fileSystem = new Filesystem();
    }

    #[Override]
    public function getBasePath(): DirectoryPath
    {
        return $this->basePath;
    }

    #[Override]
    public function exists(Path $path): bool
    {
        return $this->fileSystem->exists($this->getAbsolutePathString($path));
    }

    #[Override]
    public function createDirectory(DirectoryPath $path): void
    {
        $this->fileSystem->mkdir($this->getAbsolutePathString($path), 0755);
    }

    #[Override]
    public function touchFile(FilePath $path): void
    {
        $this->fileSystem->touch($this->getAbsolutePathString($path));
    }

    #[Override]
    public function putContent(FilePath $path, string $content): void
    {
        file_put_contents($this->getAbsolutePathString($path), $content);
    }

    #[Override]
    public function getContent(FilePath $path): string
    {
        return file_get_contents($this->getAbsolutePathString($path));
    }

    #[Override]
    public function getModificationTime(Path $path): int
    {
        return filemtime($this->getAbsolutePathString($path));
    }

    #[Override]
    public function removeFile(FilePath $path): void
    {
        $this->fileSystem->remove($this->getAbsolutePathString($path));
    }

    #[Override]
    public function removeDirectory(DirectoryPath $path, bool $ignoreEmpty = false): void
    {
        if (!$ignoreEmpty) {
            $this->assertDirectoryIsEmpty($path);
        }
        $this->fileSystem->remove($this->getAbsolutePathString($path));
    }

    #[Override]
    public function getAbsolutePath(Path $path): Path
    {
        return $path->prepend($this->basePath);
    }

    #[Override]
    public function listDirectories(DirectoryPath $root, bool $includeRoot = false, bool $recursive = true): array
    {
        /** @var list<Directory> $directories */
        $directories = [];

        if ($includeRoot) {
            $directories[] = $this->buildDirectory($root);
        }

        $finder = new Finder();
        $finder->in($this->getAbsolutePath($root)->toAbsoluteFileSystemString());
        if (!$recursive) {
            $finder->depth(0);
        }
        $finder->ignoreDotFiles(true);

        foreach ($finder->directories() as $directory) {
            $directories[] = $this->buildDirectory($root, $directory);
        }

        return $directories;
    }

    #[Override]
    public function listFiles(DirectoryPath $root, bool $recursive = true): array
    {
        /* @var File[] $files */
        $files = [];

        $finder = new Finder();
        $finder->in($this->getAbsolutePath($root)->toAbsoluteFileSystemString());
        if (!$recursive) {
            $finder->depth(0);
        }
        $finder->ignoreDotFiles(true);

        foreach ($finder->files() as $splFile) {
            /** @var SplFileInfo $splFile */
            $file = new File(
                $this->getBasePath()->toAbsoluteFileSystemString(),
                $root->toRelativeFileSystemString(),
                $splFile->getRelativePathName()
            );
            $files[] = $file;
        }

        return $files;
    }

    protected function getAbsolutePathString(Path $path): string
    {
        return $path->prepend($this->basePath)->toAbsoluteFileSystemString();
    }

    /**
     * @throws DirectoryNotEmptyException
     */
    protected function assertDirectoryIsEmpty(DirectoryPath $path): void
    {
        $absoluteDirectoryPath = $this->getAbsolutePath($path);
        $finder = new Finder();
        $finder->in($absoluteDirectoryPath->toAbsoluteString(DIRECTORY_SEPARATOR));
        $numFiles = $finder->files()->count();
        if ($numFiles > 0) {
            throw new DirectoryNotEmptyException($path);
        }
    }

    protected function buildDirectory(DirectoryPath $root, SplFileInfo $splDirectory = null): Directory
    {
        $relativeDirectoryPath = '';
        if (null !== $splDirectory) {
            $relativeDirectoryPath = $splDirectory->getRelativePathName();
        }

        $directory = new Directory(
            $this->getBasePath()->toAbsoluteFileSystemString(),
            $root->toRelativeFileSystemString(),
            $relativeDirectoryPath . DIRECTORY_SEPARATOR
        );

        return $directory;
    }
}
