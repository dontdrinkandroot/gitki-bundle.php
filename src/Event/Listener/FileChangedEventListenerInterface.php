<?php

namespace Dontdrinkandroot\GitkiBundle\Event\Listener;

use Dontdrinkandroot\GitkiBundle\Event\FileChangedEvent;

interface FileChangedEventListenerInterface
{
    public function onFileChanged(FileChangedEvent $event);
}
