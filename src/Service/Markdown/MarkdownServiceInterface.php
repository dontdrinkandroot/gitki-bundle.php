<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Markdown;

use Dontdrinkandroot\GitkiBundle\Model\Document\ParsedMarkdownDocument;
use Dontdrinkandroot\Path\FilePath;

interface MarkdownServiceInterface
{
    public function parse(string $content, FilePath $path): ParsedMarkdownDocument;
}
