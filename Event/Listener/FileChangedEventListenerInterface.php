<?php


namespace Dontdrinkandroot\GitkiBundle\Event\Listener;

use Dontdrinkandroot\GitkiBundle\Event\FileChangedEvent;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
interface FileChangedEventListenerInterface
{
    public function onFileChanged(FileChangedEvent $event);
}
