<?php

namespace Dontdrinkandroot\GitkiBundle\Model\FileInfo;

use Dontdrinkandroot\Path\FilePath;
use JsonSerializable;

class File extends AbstractPathAwareFileInfo implements JsonSerializable
{
    protected FilePath $relativePath;

    protected FilePath $absolutePath;

    protected ?string $title = null;

    public function __construct($basePath, $currentDirectoryPath, $relativeFilePath)
    {
        parent::__construct($basePath . $currentDirectoryPath . $relativeFilePath);
        $this->absolutePath = FilePath::parse($currentDirectoryPath . $relativeFilePath);
        $this->relativePath = FilePath::parse($relativeFilePath);
    }

    /**
     * @return FilePath
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * @return FilePath
     */
    public function getAbsolutePath()
    {
        return $this->absolutePath;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * {@inheritdoc}
     * @return array{path: string, name: string, extension: string|null, title: string|null}
     */
    public function jsonSerialize(): array
    {
        $absolutePath = $this->getAbsolutePath();
        $data = [
            'path' => $absolutePath->toAbsoluteString(),
            'name' => $absolutePath->getFileName(),
            'extension' => $absolutePath->getExtension(),
            'title' => $this->getTitle()
        ];

        return $data;
    }
}
