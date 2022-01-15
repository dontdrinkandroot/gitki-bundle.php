<?php


namespace Dontdrinkandroot\GitkiBundle\Model\Document;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
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
    public function setHtml($html): void
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
    public function setTitle($title): void
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
    public function setToc(array $toc): void
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
