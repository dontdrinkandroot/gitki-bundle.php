<?php


namespace Dontdrinkandroot\GitkiBundle\Markdown\Renderer;

use League\CommonMark\Extension\Table\TableRenderer;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Stringable;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class BootstrapTableRenderer implements NodeRendererInterface
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
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): Stringable
    {
        $tableElement = $this->decoratedRenderer->render($node, $childRenderer);
        $tableElement->setAttribute('class', 'table');

        return $tableElement;
    }
}
