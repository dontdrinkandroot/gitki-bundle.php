<?php

namespace Dontdrinkandroot\GitkiBundle\Event\Listener;

use Dontdrinkandroot\GitkiBundle\Event\FileMovedEvent;

interface FileMovedEventListenerInterface
{
    public function onFileMoved(FileMovedEvent $event);
}
