<?php


namespace Dontdrinkandroot\GitkiBundle\Service\FileSystem;

use Dontdrinkandroot\GitkiBundle\Exception\DirectoryNotEmptyException;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\Path;
use Dontdrinkandroot\Utils\StringUtils;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Performs file system operations relative to a base path.
 *
 * All paths passed to the functions have to be relative to the base path.
 *
 * @package Dontdrinkandroot\GitkiBundle\Service\FileSystem
 */
class FileSystemService implements FileSystemServiceInterface
{

    /**
     * @var DirectoryPath
     */
    protected $basePath;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @param string $basePath
     *
     * @throws \Exception
     */
    public function __construct($basePath)
    {
        $pathString = $basePath;

        if (!StringUtils::startsWith($pathString, '/')) {
            throw new \RuntimeException('Base Path must be absolute');
        }

        if (!StringUtils::endsWith($pathString, '/')) {
            $pathString .= '/';
        }

        $this->basePath = DirectoryPath::parse($pathString);
        $this->fileSystem = new Filesystem();
    }

    /**
     * {@inheritdoc}
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * {@inheritdoc}
     */
    public function exists(Path $path)
    {
        return $this->fileSystem->exists($this->getAbsolutePathString($path));
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectory(DirectoryPath $path)
    {
        $this->fileSystem->mkdir($this->getAbsolutePathString($path), 0755);
    }

    /**
     * {@inheritdoc}
     */
    public function touchFile(FilePath $path)
    {
        $this->fileSystem->touch($this->getAbsolutePathString($path));
    }

    /**
     * {@inheritdoc}
     */
    public function putContent(FilePath $path, $content)
    {
        file_put_contents($this->getAbsolutePathString($path), $content);
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(FilePath $relativePath)
    {
        return file_get_contents($this->getAbsolutePathString($relativePath));
    }

    /**
     * @param Path $path
     *
     * @return int
     */
    public function getModificationTime(Path $path)
    {
        return filemtime($this->getAbsolutePathString($path));
    }

    /**
     * {@inheritdoc}
     */
    public function removeFile(FilePath $path)
    {
        $this->fileSystem->remove($this->getAbsolutePathString($path));
    }

    /**
     * {@inheritdoc}
     */
    public function removeDirectory(DirectoryPath $path, $ignoreEmpty = false)
    {
        if (!$ignoreEmpty) {
            $this->assertDirectoryIsEmpty($path);
        }
        $this->fileSystem->remove($this->getAbsolutePathString($path));
    }

    /**
     * {@inheritdoc}
     */
    public function getAbsolutePath(Path $path)
    {
        return $path->prepend($this->basePath);
    }

    /**
     * {@inheritdoc}
     */
    public function listDirectories(DirectoryPath $root, $includeRoot = false, $recursive = true)
    {
        // TODO: Implement listDirectories() method.
    }

    /**
     * {@inheritdoc}
     */
    public function listFiles(DirectoryPath $root, $recursive = true)
    {
        // TODO: Implement listFiles() method.
    }

    /**
     * @param Path $path
     *
     * @return Path
     */
    protected function getAbsolutePathString(Path $path)
    {
        return $path->prepend($this->basePath)->toAbsoluteFileSystemString();
    }

    /**
     * @param DirectoryPath $path
     *
     * @throws DirectoryNotEmptyException
     */
    protected function assertDirectoryIsEmpty(DirectoryPath $path)
    {
        $absoluteDirectoryPath = $this->getAbsolutePath($path);
        $finder = new Finder();
        $finder->in($absoluteDirectoryPath->toAbsoluteString(DIRECTORY_SEPARATOR));
        $numFiles = $finder->files()->count();
        if ($numFiles > 0) {
            throw new DirectoryNotEmptyException($path);
        }
    }
}
