<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Routing;

class RouteDefinition
{

    /**
     * @var string
     */
    protected $route;

    /**
     * @var
     */
    protected $controller;

    /**
     * @param string $route
     * @param string $controller
     */
    function __construct( $controller)
    {
        $this->controller = $controller;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }
}
