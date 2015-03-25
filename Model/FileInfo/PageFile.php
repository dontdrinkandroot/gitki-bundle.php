<?php


namespace Dontdrinkandroot\GitkiBundle\Model\FileInfo;

class PageFile extends File
{

    protected $title;

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }
}
