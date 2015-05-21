<?php


namespace Dontdrinkandroot\GitkiBundle\Event\Listener;

use Dontdrinkandroot\GitkiBundle\Event\FileDeletedEvent;

interface FileDeletedEventListenerInterface
{

    public function onFileDeleted(FileDeletedEvent $event);
}
