<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Git;

use GitWrapper\Event\GitOutputEvent;
use GitWrapper\Event\GitOutputListenerInterface;

class StringOutputListener implements GitOutputListenerInterface
{
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
