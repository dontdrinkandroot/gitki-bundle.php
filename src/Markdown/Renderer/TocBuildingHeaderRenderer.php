<?php

namespace Dontdrinkandroot\GitkiBundle\Markdown\Renderer;

use Dontdrinkandroot\Common\Asserted;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Renderer\Block\HeadingRenderer;
use League\CommonMark\Node\Inline\AbstractInline;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use Override;
use Stringable;

class TocBuildingHeaderRenderer implements NodeRendererInterface
{
    private array $toc = [];

    private ?string $title = null;

    private int $count = 0;

    private array $current = [];

    private readonly HeadingRenderer $decoratedRender;

    public function __construct()
    {
        $this->decoratedRender = new HeadingRenderer();
    }

    #[Override]
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): Stringable
    {
        $heading = Asserted::instanceOf($node, Heading::class);
        $htmlElement = Asserted::instanceOf(
            $this->decoratedRender->render($heading, $childRenderer),
            HtmlElement::class
        );

        $id = 'heading' . $this->count;
        $level = $heading->getLevel();
        $text = $this->getBlockTextContent($heading);

        $htmlElement->setAttribute('id', $id);
        if (null === $this->title && $level === 1) {
            $this->title = $text;
        } elseif ($level >= 2) {
            for ($i = $level; $i <= 6; $i++) {
                unset($this->current[$i]);
            }
            $this->current[$level] = [
                'id' => $id,
                'text' => $text,
                'level' => $level,
                'children' => []
            ];
            if ($level === 2) {
                $this->toc[] = &$this->current[$level];
            } elseif (isset($this->current[$level - 1])) {
                /** @psalm-suppress PossiblyInvalidArrayOffset */
                $this->current[$level - 1]['children'][] = &$this->current[$level];
            }
        }

        $this->count++;

        return $htmlElement;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getToc(): array
    {
        return $this->toc;
    }

    private function getBlockTextContent(Heading $header): string
    {
        $text = '';
        foreach ($header->children() as $node) {
            $text .= $this->getNodeTextContent($node);
        }

        return $text;
    }

    private function getNodeTextContent(Node $node): string
    {
        if ($node instanceof Text) {
            return $node->getLiteral();
        }

        if ($node instanceof AbstractInline) {
            $text = '';
            foreach ($node->children() as $child) {
                $text .= $this->getNodeTextContent($child);
            }

            return $text;
        }

        return '';
    }
}
