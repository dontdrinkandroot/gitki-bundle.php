<?php


namespace Dontdrinkandroot\GitkiBundle\Model\Document;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class SearchResultDocument extends Document
{
    /**
     * @var float
     */
    private $score;

    /**
     * @param float $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }
}
