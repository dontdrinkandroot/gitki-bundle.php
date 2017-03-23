<?php


namespace Dontdrinkandroot\GitkiBundle\Markdown\Renderer;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\Heading;
use League\CommonMark\Block\Renderer\HeadingRenderer;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInlineContainer;
use League\CommonMark\Inline\Element\Text;
use League\CommonMark\Node\Node;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class TocBuildingHeaderRenderer extends HeadingRenderer
{
    private $toc = [];

    private $title = null;

    private $count = 0;

    private $current = [];

    /**
     * {@inheritdoc}
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if (!($block instanceof Heading)) {
            throw new \InvalidArgumentException('Incompatible block type: ' . get_class($block));
        }

        $htmlElement = parent::render($block, $htmlRenderer, $inTightList);

        $id = 'heading' . $this->count;
        $level = $block->getLevel();
        $text = $this->getBlockTextContent($block);

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

    /**
     * @param AbstractBlock $header
     *
     * @return string
     */
    private function getBlockTextContent(AbstractBlock $header)
    {
        $text = '';
        foreach ($header->children() as $node) {
            $text .= $this->getNodeTextContent($node);
        }

        return $text;
    }

    /**
     * @param Node $node
     *
     * @return string
     */
    private function getNodeTextContent(Node $node)
    {
        if ($node instanceof Text) {
            return $node->getContent();
        }

        if ($node instanceof AbstractInlineContainer) {
            $text = '';
            foreach ($node->children() as $child) {
                $text .= $this->getNodeTextContent($child);
            }

            return $text;
        }

        return '';
    }
}
