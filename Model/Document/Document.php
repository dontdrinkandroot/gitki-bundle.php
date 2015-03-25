<?php

namespace Dontdrinkandroot\GitkiBundle\Model\Document;

use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\Path;

class Document
{

    /**
     * @var FilePath
     */
    private $path;

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

    public function __construct(FilePath $path)
    {
        $this->path = $path;
    }

    /**
     * @return FilePath
     */
    public function getPath()
    {
        return $this->path;
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
    public function setTitle($title)
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
     *
     * @return null
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @param Path[] $pageLinks
     */
    public function setLinkedPaths($pageLinks)
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
