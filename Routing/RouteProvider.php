<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Routing;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class RouteProvider implements RouteProviderInterface
{

    protected $directoryActions = [];

    protected $fileTypeActions = [];

    /**
     * {@inheritdoc}
     */
    public function resolveDirectoryAction($action)
    {
        if (!isset($this->directoryActions[$action])) {
            throw new ResourceNotFoundException();
        }

        return $this->directoryActions[$action];
    }

    /**
     * {@inheritdoc}
     */
    public function resolveFileAction($action, $extension)
    {
        if (isset($this->fileTypeActions[$extension][$action])) {
            return $this->fileTypeActions[$extension][$action];
        }

        if (!isset($this->fileTypeActions[''][$action])) {
            throw new ResourceNotFoundException();
        }

        return $this->fileTypeActions[''][$action];
    }

    public function registerDirectoryAction($controller, $action = '')
    {
        $this->directoryActions[$action] = new RouteDefinition($controller);
    }

    public function registerFileAction($controller, $action = '', $extension = '')
    {
        $this->fileTypeActions[$extension][$action] = new RouteDefinition($controller);
    }
}
