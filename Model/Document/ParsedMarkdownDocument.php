<?php


namespace Dontdrinkandroot\GitkiBundle\Model\Document;

class ParsedMarkdownDocument extends Document
{

    private $html;

    private $title;

    private $toc;

    public function setHtml($html)
    {
        $this->html = $html;
    }

    public function getHtml()
    {
        return $this->html;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setToc($toc)
    {
        $this->toc = $toc;
    }

    public function getToc()
    {
        return $this->toc;
    }
}
