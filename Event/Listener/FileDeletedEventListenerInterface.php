<?php


namespace Dontdrinkandroot\GitkiBundle\Event\Listener;

use Dontdrinkandroot\GitkiBundle\Event\FileDeletedEvent;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
interface FileDeletedEventListenerInterface
{
    public function onFileDeleted(FileDeletedEvent $event);
}
