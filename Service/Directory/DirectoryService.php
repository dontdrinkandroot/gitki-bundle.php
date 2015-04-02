<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Directory;

use Dontdrinkandroot\GitkiBundle\Model\DirectoryListing;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\Directory;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\File;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\PageFile;
use Dontdrinkandroot\GitkiBundle\Repository\ElasticsearchRepositoryInterface;
use Dontdrinkandroot\GitkiBundle\Repository\GitRepositoryInterface;
use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class DirectoryService implements DirectoryServiceInterface
{

    /**
     * @var GitRepositoryInterface
     */
    private $gitRepository;

    /** @var string[] */
    private $indexFiles = [];

    /**
     * @var ElasticsearchRepositoryInterface
     */
    private $elasticsearchRepository;

    /**
     * @param GitRepositoryInterface           $gitRepository
     * @param ElasticsearchRepositoryInterface $elasticsearchRepository
     */
    public function __construct(
        GitRepositoryInterface $gitRepository,
        ElasticsearchRepositoryInterface $elasticsearchRepository
    ) {
        $this->gitRepository = $gitRepository;
        $this->elasticsearchRepository = $elasticsearchRepository;
    }

    /**
     * @param string[] $indexFiles
     */
    public function setIndexFiles(array $indexFiles)
    {
        $this->indexFiles = $indexFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveIndexFile(DirectoryPath $directoryPath)
    {
        foreach ($this->indexFiles as $indexFile) {
            $filePath = $directoryPath->appendFile($indexFile);
            if ($this->gitRepository->exists($filePath)) {
                return $filePath;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function listDirectory(DirectoryPath $relativeDirectoryPath)
    {
        $files = $this->listFiles($relativeDirectoryPath);
        $subDirectories = $this->listSubdirectories($relativeDirectoryPath);

        usort(
            $subDirectories,
            function (Directory $a, Directory $b) {
                return strcmp($a->getFilename(), $b->getFilename());
            }
        );

        usort(
            $files,
            function (File $a, File $b) {
                return strcmp($a->getFilename(), $b->getFilename());
            }
        );

        return new DirectoryListing($relativeDirectoryPath, $subDirectories, $files);
    }

    /**
     * @param DirectoryPath $relativeDirectoryPath
     *
     * @return Directory[]
     */
    protected function listSubdirectories(
        DirectoryPath $relativeDirectoryPath
    ) {
        $subDirectories = [];
        $finder = new Finder();
        $finder->in(
            $this->gitRepository->getAbsolutePath($relativeDirectoryPath)->toAbsoluteString(DIRECTORY_SEPARATOR)
        );
        $finder->depth(0);
        $finder->ignoreDotFiles(true);
        foreach ($finder->directories() as $directory) {
            /* @var SplFileInfo $directory */
            $subDirectory = new Directory(
                $this->gitRepository->getRepositoryPath()->toAbsoluteString(DIRECTORY_SEPARATOR),
                $relativeDirectoryPath->toRelativeString(DIRECTORY_SEPARATOR),
                $directory->getRelativePathName() . DIRECTORY_SEPARATOR
            );
            $subDirectories[] = $subDirectory;
        }

        return $subDirectories;
    }

    /**
     * @param DirectoryPath $relativeDirectoryPath
     *
     * @return File[]
     */
    protected function listFiles(DirectoryPath $relativeDirectoryPath)
    {
        /* @var File[] $files */
        $files = [];

        $finder = new Finder();
        $finder->in(
            $this->gitRepository->getAbsolutePath($relativeDirectoryPath)->toAbsoluteString(DIRECTORY_SEPARATOR)
        );
        $finder->depth(0);
        foreach ($finder->files() as $splFile) {
            /* @var SplFileInfo $splFile */
            if ($splFile->getExtension() != 'lock') {
                $file = new File(
                    $this->gitRepository->getRepositoryPath()->toAbsoluteString(DIRECTORY_SEPARATOR),
                    $relativeDirectoryPath->toRelativeString(DIRECTORY_SEPARATOR),
                    $splFile->getRelativePathName()
                );
                $title = $this->elasticsearchRepository->findTitle($file->getAbsolutePath());
                if (null !== $title) {
                    $file->setTitle($title);
                }
                $files[] = $file;
            }
        }

        return $files;
    }
}
