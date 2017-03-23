<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Git;

use Dontdrinkandroot\GitkiBundle\Model\CommitMetadata;
use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\GitkiBundle\Repository\LogParser;
use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemServiceInterface;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\Path;
use GitWrapper\GitWrapper;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class GitService implements GitServiceInterface
{
    /**
     * @var FileSystemServiceInterface
     */
    private $fileSystemService;

    public function __construct(FileSystemServiceInterface $fileSystemService)
    {
        $this->fileSystemService = $fileSystemService;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepositoryPath()
    {
        return $this->fileSystemService->getBasePath();
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkingCopyHistory($maxCount = null)
    {
        return $this->getHistory(null, $maxCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getFileHistory(FilePath $path, $maxCount = null)
    {
        return $this->getHistory($path, $maxCount);
    }

    public function getHistory(FilePath $path = null, $maxCount = null)
    {
        $options = ['pretty' => 'format:' . LogParser::getFormatString()];
        if (null !== $maxCount) {
            $options['max-count'] = $maxCount;
        }
        if (null !== $path) {
            $options['p'] = $path->toRelativeFileSystemString();
        }

        $workingCopy = $this->getWorkingCopy();
        $workingCopy->log($options);
        $log = $workingCopy->getOutput();

        return $this->parseLog($log);
    }

    /**
     * {@inheritdoc}
     */
    public function removeAndCommit(GitUserInterface $author, $paths, $commitMessage)
    {
        $this->remove($this->toFilePathArray($paths));
        $this->commit($author, $commitMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function moveAndCommit(GitUserInterface $author, FilePath $oldPath, FilePath $newPath, $commitMessage)
    {
        $workingCopy = $this->getWorkingCopy();
        $workingCopy->mv($oldPath->toRelativeFileSystemString(), $newPath->toRelativeFileSystemString());
        $this->commit($author, $commitMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(Path $path)
    {
        return $this->fileSystemService->exists($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getAbsolutePath(Path $path)
    {
        return $path->prepend($this->getRepositoryPath());
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectory(DirectoryPath $relativePath)
    {
        $this->fileSystemService->createDirectory($relativePath);
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(FilePath $relativePath)
    {
        return file_get_contents($this->getAbsolutePathString($relativePath));
    }

    /**
     * {@inheritdoc}
     */
    public function removeDirectory(DirectoryPath $path)
    {
        $this->fileSystemService->removeDirectory($path);
    }

    /**
     * {@inheritdoc}
     */
    public function putAndCommitFile($author, FilePath $path, $content, $commitMessage)
    {
        $this->fileSystemService->putContent($path, $content);
        $this->add([$path]);
        $this->commit($author, $commitMessage);
    }

    /**
     * @param GitUserInterface $author
     * @param FilePath         $path
     * @param UploadedFile     $uploadedFile
     *
     * @param string $commitMessage
     *
     * @return mixed
     */
    public function addAndCommitUploadedFile($author, FilePath $path, UploadedFile $uploadedFile, $commitMessage)
    {
        $uploadedFile->move(
            $this->fileSystemService->getAbsolutePath($path->getParentPath())->toAbsoluteFileSystemString(),
            $path->getName()
        );

        $this->addAndCommitFile($author, $commitMessage, $path);
    }

    /**
     * @return \GitWrapper\GitWorkingCopy
     */
    protected function getWorkingCopy()
    {
        $git = new GitWrapper();
        $workingCopy = $git->workingCopy($this->fileSystemService->getBasePath()->toAbsoluteFileSystemString());

        return $workingCopy;
    }

    /**
     * @param Path $relativePath
     *
     * @return string
     */
    protected function getAbsolutePathString(Path $relativePath)
    {
        return $this->getAbsolutePath($relativePath)->toAbsoluteFileSystemString();
    }

    /**
     * @param FilePath[]|FilePath $paths
     *
     * @return FilePath[]
     */
    protected function toFilePathArray($paths)
    {
        if (!is_array($paths)) {
            return [$paths];
        } else {
            return $paths;
        }
    }

    /**
     * @param GitUserInterface $user
     *
     * @return string
     */
    protected function getAuthorString(GitUserInterface $user)
    {
        return sprintf('"%s <%s>"', $user->getGitUserName(), $user->getGitUserEmail());
    }

    /**
     * @param string $log
     *
     * @return CommitMetadata[]
     */
    protected function parseLog($log)
    {
        preg_match_all(LogParser::getMatchString(), $log, $matches);
        $metaData = [];
        $numEntries = count($matches[1]);
        for ($i = 0; $i < $numEntries; $i++) {
            $hash = $matches[1][$i];
            $name = $matches[2][$i];
            $eMail = $matches[3][$i];
            $timeStamp = (int)$matches[4][$i];
            $message = $matches[5][$i];
            $metaData[] = new CommitMetadata($hash, $name, $eMail, $timeStamp, $message);
        }

        return $metaData;
    }

    /**
     * @param GitUserInterface $author
     * @param string           $commitMessage
     */
    protected function commit(GitUserInterface $author, $commitMessage)
    {
        $this->getWorkingCopy()->commit(
            [
                'm'      => $commitMessage,
                'author' => $this->getAuthorString($author)
            ]
        );
    }

    /**
     * @param FilePath[] $paths
     */
    protected function add(array $paths)
    {
        $workingCopy = $this->getWorkingCopy();
        foreach ($paths as $path) {
            $workingCopy->add($this->escapePath($path));
        }
    }

    /**
     * @param FilePath[] $paths
     */
    protected function remove(array $paths)
    {
        $workingCopy = $this->getWorkingCopy();
        foreach ($paths as $path) {
            $workingCopy->rm($this->escapePath($path));
        }
    }

    /**
     * @param GitUserInterface $author
     * @param string           $commitMessage
     * @param FilePath         $path
     */
    protected function addAndCommitFile(GitUserInterface $author, $commitMessage, FilePath $path)
    {
        $this->add([$path]);
        $this->commit($author, $commitMessage);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    protected function escapePath(Path $path)
    {
        return str_replace(" ", "\\ ", $path->toRelativeFileSystemString());
    }
}
