<?php

namespace Dontdrinkandroot\GitkiBundle\Markdown\Renderer;

use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\HtmlInline;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;

class EscapingHtmlInlineRenderer implements InlineRendererInterface
{
    /**
     * {@inheritdoc}
     */
    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        if (!($inline instanceof HtmlInline)) {
            throw new \InvalidArgumentException('Incompatible inline type: ' . get_class($inline));
        }

        return htmlspecialchars($inline->getContent(), ENT_HTML5);
    }
}
