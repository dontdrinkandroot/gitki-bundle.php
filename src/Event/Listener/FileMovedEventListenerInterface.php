<?php

namespace Dontdrinkandroot\GitkiBundle\Event\Listener;

use Dontdrinkandroot\GitkiBundle\Event\FileMovedEvent;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
interface FileMovedEventListenerInterface
{
    public function onFileMoved(FileMovedEvent $event);
}
