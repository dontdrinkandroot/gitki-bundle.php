<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Markdown;

use Dontdrinkandroot\GitkiBundle\Model\ParsedMarkdownDocument;
use Dontdrinkandroot\Path\FilePath;

interface MarkdownServiceInterface
{

    /**
     * @param FilePath $path
     * @param string   $content
     *
     * @return ParsedMarkdownDocument
     */
    public function parse(FilePath $path, $content);
}
