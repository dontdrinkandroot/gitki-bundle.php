<?php


namespace Dontdrinkandroot\GitkiBundle\Markdown\Renderer;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlRendererInterface;
use Webuni\CommonMark\TableExtension\TableRenderer;

class BootstrapTableRenderer extends TableRenderer
{

    /**
     * {@inheritdoc}
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        $tableElement = parent::render($block, $htmlRenderer, $inTightList);
        $tableElement->setAttribute('class', 'table');

        return $tableElement;
    }
}