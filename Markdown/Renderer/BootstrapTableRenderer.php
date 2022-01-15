<?php


namespace Dontdrinkandroot\GitkiBundle\Markdown\Renderer;

use League\CommonMark\Extension\Table\TableRenderer;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Stringable;

class BootstrapTableRenderer implements NodeRendererInterface
{
    private TableRenderer $decoratedRenderer;

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
        assert($tableElement instanceof HtmlElement);
        $tableElement->setAttribute('class', 'table');

        return $tableElement;
    }
}
