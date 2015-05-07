<?php


namespace Dontdrinkandroot\GitkiBundle\Model\FileInfo;

use Dontdrinkandroot\Path\DirectoryPath;

class Directory extends AbstractPathAwareFileInfo implements \JsonSerializable
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
    function jsonSerialize()
    {
        return [
            'path' => $this->getAbsolutePath()->toAbsoluteString()
        ];
    }
}
