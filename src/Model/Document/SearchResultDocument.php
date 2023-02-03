<?php

namespace Dontdrinkandroot\GitkiBundle\Model\Document;

use Dontdrinkandroot\Path\FilePath;

class SearchResultDocument extends Document
{
    public function __construct(FilePath $path, string $title, public readonly float $score, ?string $content = null)
    {
        parent::__construct($path, $title, $content);
    }
}
