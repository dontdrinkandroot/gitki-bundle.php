<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Routing;

interface ActionResolverInterface
{

    /**
     * @param string $action
     *
     * @return string
     */
    public function resolveDirectoryAction($action);

    /**
     * @param string $action
     * @param string $extension
     *
     * @return string
     */
    public function resolveFileAction($action, $extension);
}