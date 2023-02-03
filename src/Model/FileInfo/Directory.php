<?php

namespace Dontdrinkandroot\GitkiBundle\Model\FileInfo;

use Dontdrinkandroot\Path\DirectoryPath;
use JsonSerializable;

class Directory extends AbstractPathAwareFileInfo implements JsonSerializable
{
    protected DirectoryPath $relativePath;

    protected DirectoryPath $absolutePath;

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
     * @return array{path: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'path' => $this->getAbsolutePath()->toAbsoluteString()
        ];
    }
}
