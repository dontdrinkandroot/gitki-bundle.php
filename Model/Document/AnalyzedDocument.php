<?php

namespace Dontdrinkandroot\GitkiBundle\Model\Document;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
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
     * @return null
     */
    public function setAnalyzedContent($analyzedContent)
    {
        $this->analyzedContent = $analyzedContent;
    }
}
