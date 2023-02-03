<?php

namespace Dontdrinkandroot\GitkiBundle\Model\Document;

use Dontdrinkandroot\Path\FilePath;

class ParsedMarkdownDocument extends Document
{
    public function __construct(
        FilePath $path,
        public readonly array $toc,
        public readonly string $html,
        ?string $title = null,
        ?string $content = null
    ) {
        parent::__construct($path, $title, $content);
    }
}
