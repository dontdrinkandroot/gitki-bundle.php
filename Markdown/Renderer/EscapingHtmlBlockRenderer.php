<?php

namespace Dontdrinkandroot\GitkiBundle\Markdown\Renderer;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Element\HtmlBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class EscapingHtmlBlockRenderer implements BlockRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        if (!($block instanceof HtmlBlock)) {
            throw new \InvalidArgumentException('Incompatible block type: ' . get_class($block));
        }

        return htmlspecialchars($block->getStringContent(), ENT_HTML5);
    }
}
