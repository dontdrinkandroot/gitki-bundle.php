<?php

namespace Dontdrinkandroot\GitkiBundle\Model\Document;

use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\Path;

class Document
{
    /**
     * @var Path[]
     */
    private array $linkedPaths = [];

    public function __construct(
        public readonly FilePath $path,
        public readonly ?string $title = null,
        public readonly ?string $content = null
    ) {
    }

    /**
     * @param Path[] $pageLinks
     */
    public function setLinkedPaths(array $pageLinks): void
    {
        $this->linkedPaths = $pageLinks;
    }

    /**
     * @return Path[]
     */
    public function getLinkedPaths(): array
    {
        return $this->linkedPaths;
    }
}
