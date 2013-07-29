<?php

namespace PHPDocSearch\Web;

class Router
{
    private $controllerFactory;

    public function __construct(ControllerFactory $controllerFactory)
    {
        $this->controllerFactory = $controllerFactory;
    }

    public function route($request)
    {
        switch ($request->getPath()) {
            case '':
                return $this->controllerFactory->createPageController($request);

            case 'search':
                return $this->controllerFactory->createSearchController($request);

            default:
                return $this->controllerFactory->createUnknownRouteController($request);
        }
    }
}
