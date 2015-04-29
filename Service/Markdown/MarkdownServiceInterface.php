<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Markdown;

use Dontdrinkandroot\GitkiBundle\Model\Document\ParsedMarkdownDocument;
use Dontdrinkandroot\Path\FilePath;

interface MarkdownServiceInterface
{

    /**
     * @param string        $content
     *
     * @param FilePath|null $path The path of the document to parse. Used to resolve references.
     *
     * @return ParsedMarkdownDocument
     */
    public function parse($content, FilePath $path);
}
