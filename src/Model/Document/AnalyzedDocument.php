<?php

namespace Dontdrinkandroot\GitkiBundle\Model\Document;

class AnalyzedDocument extends Document
{
    /**
     * @var string
     */
    private $analyzedContent;

    /**
     * @return string
     */
    public function getAnalyzedContent()
    {
        return $this->analyzedContent;
    }

    /**
     * @param string $analyzedContent
     *
     * @return void
     */
    public function setAnalyzedContent($analyzedContent): void
    {
        $this->analyzedContent = $analyzedContent;
    }
}
