<?php

namespace Dontdrinkandroot\GitkiBundle\Event\Listener;

use Dontdrinkandroot\GitkiBundle\Event\FileRemovedEvent;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
interface FileRemovedEventListenerInterface
{
    public function onFileRemoved(FileRemovedEvent $event);
}
