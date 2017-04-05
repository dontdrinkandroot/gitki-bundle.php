<?php


namespace Dontdrinkandroot\GitkiBundle\Service\FileSystem;

use Dontdrinkandroot\GitkiBundle\Model\FileInfo\Directory;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\File;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\Path;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
interface FileSystemServiceInterface
{
    /**
     * @return DirectoryPath
     */
    public function getBasePath();

    /**
     * @param Path $path
     *
     * @return bool
     */
    public function exists(Path $path): bool;

    /**
     * @param DirectoryPath $path
     */
    public function createDirectory(DirectoryPath $path);

    /**
     * @param FilePath $path
     */
    public function touchFile(FilePath $path);

    /**
     * @param FilePath $path
     * @param string   $content
     */
    public function putContent(FilePath $path, $content);

    /**
     * @param Path $path
     *
     * @return int
     */
    public function getModificationTime(Path $path);

    /**
     * @param FilePath $path
     *
     * @return string
     */
    public function getContent(FilePath $path);

    /**
     * @param FilePath $path
     */
    public function removeFile(FilePath $path);

    /**
     * @param DirectoryPath $path
     * @param bool          $ignoreEmpty
     */
    public function removeDirectory(DirectoryPath $path, $ignoreEmpty = false);

    /**
     * @param Path $path
     *
     * @return Path
     */
    public function getAbsolutePath(Path $path);

    /**
     * @param DirectoryPath $root
     * @param bool          $includeRoot
     * @param bool          $recursive
     *
     * @return Directory[]
     */
    public function listDirectories(DirectoryPath $root, $includeRoot = false, $recursive = true);

    /**
     * @param DirectoryPath $root
     * @param bool          $recursive
     *
     * @return File[]
     */
    public function listFiles(DirectoryPath $root, $recursive = true);
}
