<?php

namespace Dontdrinkandroot\GitkiBundle\Markdown\Renderer;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Override;
use Stringable;

class EscapingHtmlBlockRenderer implements NodeRendererInterface
{
    #[Override]
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): Stringable|string|null
    {
        if (!($node instanceof HtmlBlock)) {
            throw new InvalidArgumentException('Incompatible block type: ' . $node::class);
        }

        return htmlspecialchars($node->getLiteral(), ENT_HTML5);
    }
}
