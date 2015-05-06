<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Directory;

use Dontdrinkandroot\GitkiBundle\Model\DirectoryListing;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\Directory;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\File;
use Dontdrinkandroot\GitkiBundle\Model\FileInfo\PageFile;
use Dontdrinkandroot\GitkiBundle\Repository\ElasticsearchRepositoryInterface;
use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemServiceInterface;
use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class DirectoryService implements DirectoryServiceInterface
{

    /**
     * @var FileSystemServiceInterface
     */
    private $fileSystemService;

    /** @var string[] */
    private $indexFiles = [];

    /**
     * @var ElasticsearchRepositoryInterface
     */
    private $elasticsearchRepository;

    /**
     * @param FileSystemServiceInterface $fileSystemService
     * @param ElasticsearchRepositoryInterface $elasticsearchRepository
     */
    public function __construct(
        FileSystemServiceInterface $fileSystemService,
        ElasticsearchRepositoryInterface $elasticsearchRepository
    ) {
        $this->fileSystemService = $fileSystemService;
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
            if ($this->fileSystemService->exists($filePath)) {
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
            $this->fileSystemService->getAbsolutePath($relativeDirectoryPath)->toAbsoluteFileSystemString()
        );
        $finder->depth(0);
        $finder->ignoreDotFiles(true);
        foreach ($finder->directories() as $directory) {
            /* @var SplFileInfo $directory */
            $subDirectory = new Directory(
                $this->fileSystemService->getBasePath()->toAbsoluteFileSystemString(),
                $relativeDirectoryPath->toRelativeFileSystemString(),
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
            $this->fileSystemService->getAbsolutePath($relativeDirectoryPath)->toAbsoluteFileSystemString()
        );
        $finder->depth(0);
        foreach ($finder->files() as $splFile) {
            /** @var SplFileInfo $splFile */
            if ($splFile->getExtension() != 'lock') {
                $file = new File(
                    $this->fileSystemService->getBasePath()->toAbsoluteFileSystemString(),
                    $relativeDirectoryPath->toRelativeFileSystemString(),
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

    /**
     * {@inheritdoc}
     */
    public function findSubDirectories(DirectoryPath $rootPath, $includeRoot = true)
    {
        $subDirectories = [];
        if ($includeRoot) {
            $subDirectories[] = $rootPath;
        }

        $basePath = $this->fileSystemService->getAbsolutePath($rootPath);
        $finder = new Finder();
        $finder->in($basePath->toAbsoluteFileSystemString());
        foreach ($finder->directories() as $splFile) {
            /** @var SplFileInfo $splFile */
            $subDirectory = DirectoryPath::parse(
                $splFile->getRelativePathname() . DIRECTORY_SEPARATOR,
                DIRECTORY_SEPARATOR
            );
            $subDirectories[] = $subDirectory;
        }

        return $subDirectories;
    }
}
