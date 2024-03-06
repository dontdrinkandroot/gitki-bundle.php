<?php

namespace Dontdrinkandroot\GitkiBundle\Markdown\Renderer;

use Dontdrinkandroot\Common\Asserted;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Extension\Table\TableRenderer;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Override;
use Stringable;

class BootstrapTableRenderer implements NodeRendererInterface
{
    private readonly TableRenderer $decoratedRenderer;

    public function __construct()
    {
        $this->decoratedRenderer = new TableRenderer();
    }

    #[Override]
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): Stringable
    {
        $tableElement = $this->decoratedRenderer->render(Asserted::instanceOf($node, Table::class), $childRenderer);
        assert($tableElement instanceof HtmlElement);
        $tableElement->setAttribute('class', 'table');

        return $tableElement;
    }
}
