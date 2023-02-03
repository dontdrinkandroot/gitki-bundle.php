<?php

namespace Dontdrinkandroot\GitkiBundle\Markdown\Renderer;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Inline\HtmlInline;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Stringable;

class EscapingHtmlInlineRenderer implements NodeRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): Stringable|string|null
    {
        if (!($node instanceof HtmlInline)) {
            throw new InvalidArgumentException('Incompatible inline type: ' . $node::class);
        }

        return htmlspecialchars($node->getLiteral(), ENT_HTML5);
    }
}
