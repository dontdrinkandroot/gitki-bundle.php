<?php

namespace Dontdrinkandroot\GitkiBundle\Model\Document;

use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\Path;

class Document
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $content;

    /**
     * @var Path[]
     */
    private $linkedPaths;

    public function __construct(public readonly FilePath $path)
    {
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content): void
    {
        $this->content = $content;
    }

    /**
     * @param Path[] $pageLinks
     */
    public function setLinkedPaths($pageLinks): void
    {
        $this->linkedPaths = $pageLinks;
    }

    /**
     * @return Path[]
     */
    public function getLinkedPaths()
    {
        return $this->linkedPaths;
    }
}
