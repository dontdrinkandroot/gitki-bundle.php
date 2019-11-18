<?php


namespace Dontdrinkandroot\GitkiBundle\Markdown\Renderer;

use League\CommonMark\Block\Element\AbstractBlock;
use League\CommonMark\Block\Renderer\BlockRendererInterface;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Ext\Table\TableRenderer;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class BootstrapTableRenderer implements BlockRendererInterface
{
    /** @var TableRenderer */
    private $decoratedRenderer;

    public function __construct()
    {
        $this->decoratedRenderer = new TableRenderer();
    }

    /**
     * {@inheritdoc}
     */
    public function render(AbstractBlock $block, ElementRendererInterface $htmlRenderer, $inTightList = false)
    {
        $tableElement = $this->decoratedRenderer->render($block, $htmlRenderer, $inTightList);
        $tableElement->setAttribute('class', 'table');

        return $tableElement;
    }
}
