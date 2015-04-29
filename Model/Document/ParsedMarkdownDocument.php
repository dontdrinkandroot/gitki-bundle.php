<?php


namespace Dontdrinkandroot\GitkiBundle\Model\Document;

class ParsedMarkdownDocument extends Document
{

    /**
     * @var string
     */
    private $html;

    /**
     * @var string
     */
    private $title;

    /**
     * @var array
     */
    private $toc;

    /**
     * @param string $html
     */
    public function setHtml($html)
    {
        $this->html = $html;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param array $toc
     */
    public function setToc(array $toc)
    {
        $this->toc = $toc;
    }

    /**
     * @return array
     */
    public function getToc()
    {
        return $this->toc;
    }
}
