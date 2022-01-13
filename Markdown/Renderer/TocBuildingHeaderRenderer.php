<?php


namespace Dontdrinkandroot\GitkiBundle\Markdown\Renderer;

use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Renderer\Block\HeadingRenderer;
use League\CommonMark\Node\Inline\AbstractInline;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use Stringable;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class TocBuildingHeaderRenderer implements NodeRendererInterface
{
    private $toc = [];

    private $title = null;

    private $count = 0;

    private $current = [];

    /** @var HeadingRenderer */
    private $decoratedRender;

    public function __construct()
    {
        $this->decoratedRender = new HeadingRenderer();
    }

    /**
     * {@inheritdoc}
     */
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): Stringable
    {
        $htmlElement = $this->decoratedRender->render($node, $childRenderer);

        $id = 'heading' . $this->count;
        $level = $node->getLevel();
        $text = $this->getBlockTextContent($node);

        $htmlElement->setAttribute('id', $id);
        if (null === $this->title && $level == 1) {
            $this->title = $text;
        } else {
            if ($level >= 2) {
                for ($i = $level; $i <= 6; $i++) {
                    unset($this->current[$i]);
                }
                $this->current[$level] = [
                    'id'       => $id,
                    'text'     => $text,
                    'level'    => $level,
                    'children' => []
                ];
                if ($level == 2) {
                    $this->toc[] = &$this->current[$level];
                } else {
                    if (isset($this->current[$level - 1])) {
                        $this->current[$level - 1]['children'][] = &$this->current[$level];
                    }
                }
            }
        }

        $this->count++;

        return $htmlElement;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getToc()
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
