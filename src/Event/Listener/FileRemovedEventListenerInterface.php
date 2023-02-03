<?php

namespace Dontdrinkandroot\GitkiBundle\Event\Listener;

use Dontdrinkandroot\GitkiBundle\Event\FileRemovedEvent;

interface FileRemovedEventListenerInterface
{
    public function onFileRemoved(FileRemovedEvent $event);
}
