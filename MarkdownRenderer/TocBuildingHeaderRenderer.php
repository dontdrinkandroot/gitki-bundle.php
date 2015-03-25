<?php


namespace Dontdrinkandroot\GitkiBundle\MarkdownRenderer;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\Header;
use League\CommonMark\Block\Renderer\HeaderRenderer;
use League\CommonMark\HtmlElement;
use League\CommonMark\HtmlRendererInterface;

class TocBuildingHeaderRenderer extends HeaderRenderer
{

    private $toc = [];

    private $title = null;

    private $count = 0;

    private $current = [];

    /**
     * @param AbstractBlock         $block
     * @param HtmlRendererInterface $htmlRenderer
     * @param bool                  $inTightList
     *
     * @return HtmlElement
     */
    public function render(AbstractBlock $block, HtmlRendererInterface $htmlRenderer, $inTightList = false)
    {
        if (!($block instanceof Header)) {
            throw new \InvalidArgumentException('Incompatible block type: ' . get_class($block));
        }

        $htmlElement = parent::render($block, $htmlRenderer, $inTightList);

        $id = 'heading' . $this->count;
        $level = $block->getLevel();
        $text = $htmlElement->getContents();

        $htmlElement->setAttribute('id', $id);
        if (null === $this->title && $level == 1) {
            $this->title = $text;
        } else {
            if ($level >= 2) {
                for ($i = $level; $i <= 6; $i++) {
                    unset($this->current[$i]);
                }
                $this->current[$level] = [
                    'id'       => $id,
                    'text'     => $text,
                    'level'    => $level,
                    'children' => []
                ];
                if ($level == 2) {
                    $this->toc[] = &$this->current[$level];
                } else {
                    if (isset($this->current[$level - 1])) {
                        $this->current[$level - 1]['children'][] = &$this->current[$level];
                    }
                }
            }
        }

        $this->count++;

        return $htmlElement;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getToc()
    {
        return $this->toc;
    }
}
