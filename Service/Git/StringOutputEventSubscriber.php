<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Git;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symplify\GitWrapper\Event\GitOutputEvent;

class StringOutputEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            GitOutputEvent::class => 'handleOutput',
        ];
    }

    /**
     * @var string
     */
    private $buffer = '';

    public function handleOutput(GitOutputEvent $gitOutputEvent): void
    {
        $this->buffer .= $gitOutputEvent->getBuffer();
    }

    /**
     * @return string
     */
    public function getBuffer(): string
    {
        return $this->buffer;
    }
}
