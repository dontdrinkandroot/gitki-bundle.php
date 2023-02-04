<?php

namespace Dontdrinkandroot\GitkiBundle\Model\FileInfo;

use Dontdrinkandroot\Path\FilePath;
use JsonSerializable;

class File extends AbstractPathAwareFileInfo implements JsonSerializable
{
    public readonly FilePath $relativePath;

    public readonly FilePath $absolutePath;

    protected ?string $title = null;

    public function __construct($basePath, $currentDirectoryPath, $relativeFilePath)
    {
        parent::__construct($basePath . $currentDirectoryPath . $relativeFilePath);
        $this->absolutePath = FilePath::parse($currentDirectoryPath . $relativeFilePath);
        $this->relativePath = FilePath::parse($relativeFilePath);
    }

    /**
     * {@inheritdoc}
     */
    public function getRelativePath(): FilePath
    {
        return $this->relativePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getAbsolutePath(): FilePath
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
