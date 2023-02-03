<?php

namespace Dontdrinkandroot\GitkiBundle\Model\Document;

use Dontdrinkandroot\Path\FilePath;

class AnalyzedDocument extends Document
{
    public function __construct(
        FilePath $path,
        public readonly string $analyzedContent,
        ?string $title = null,
        ?string $content = null
    ) {
        parent::__construct($path, $title, $content);
    }
}
