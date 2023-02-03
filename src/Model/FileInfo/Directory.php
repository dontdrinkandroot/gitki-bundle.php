<?php

namespace Dontdrinkandroot\GitkiBundle\Model\FileInfo;

use Dontdrinkandroot\Path\DirectoryPath;
use JsonSerializable;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class Directory extends AbstractPathAwareFileInfo implements JsonSerializable
{
    /**
     * @var DirectoryPath
     */
    protected $relativePath;

    /**
     * @var DirectoryPath
     */
    protected $absolutePath;

    public function __construct($basePath, $currentDirectoryPath, $relativeDirectoryPath)
    {
        parent::__construct($basePath . $currentDirectoryPath . $relativeDirectoryPath);
        $this->absolutePath = DirectoryPath::parse($currentDirectoryPath . $relativeDirectoryPath);
        $this->relativePath = DirectoryPath::parse($relativeDirectoryPath);
    }

    /**
     * @return DirectoryPath
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * @return DirectoryPath
     */
    public function getAbsolutePath()
    {
        return $this->absolutePath;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return [
            'path' => $this->getAbsolutePath()->toAbsoluteString()
        ];
    }
}
