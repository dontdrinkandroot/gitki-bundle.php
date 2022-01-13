<?php

namespace Dontdrinkandroot\GitkiBundle\Markdown\Renderer;

use InvalidArgumentException;
use League\CommonMark\Extension\CommonMark\Node\Block\HtmlBlock;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class EscapingHtmlBlockRenderer implements NodeRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (!($node instanceof HtmlBlock)) {
            throw new InvalidArgumentException('Incompatible block type: ' . get_class($node));
        }

        return htmlspecialchars($node->getLiteral(), ENT_HTML5);
    }
}
