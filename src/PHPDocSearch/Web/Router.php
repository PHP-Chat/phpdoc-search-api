<?php

namespace PHPDocSearch\Web;

use \PHPDocSearch\Web\Controllers\Controller;

class Router
{
    /**
     * @var ControllerFactory
     */
    private $controllerFactory;

    /**
     * Constructor
     *
     * @param ControllerFactory $controllerFactory
     */
    public function __construct(ControllerFactory $controllerFactory)
    {
        $this->controllerFactory = $controllerFactory;
    }

    /**
     * Route a request
     *
     * @param Request $request
     * @return Controller
     */
    public function route(Request $request)
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
