<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Routing;

use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;

interface ActionResolverInterface
{

    /**
     * @param string $action
     *
     * @return RouteDefinition
     */
    public function resolveDirectoryAction($action);

    /**
     * @param string $action
     * @param string $extension
     *
     * @return RouteDefinition
     */
    public function resolveFileAction($action, $extension);
}