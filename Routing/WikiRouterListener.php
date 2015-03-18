<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Routing;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WikiRouterListener extends RouterListener {

    public function onKernelRequest(GetResponseEvent $event)
    {
        try {
            parent::onKernelRequest($event);
        } catch(NotFoundHttpException $e) {
        }
    }

}
