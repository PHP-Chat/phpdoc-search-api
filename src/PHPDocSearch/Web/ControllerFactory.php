<?php

namespace PHPDocSearch\Web;

use \PHPDocSearch\Web\Controllers\SearchController,
    \PHPDocSearch\Web\Controllers\IndexController,
    \PHPDocSearch\Web\Controllers\UnknownRouteController;

class ControllerFactory
{
    public function createPageController(Request $request)
    {
        return new PageController(new ViewFactory, new ContentTypeManager, $request);
    }

    public function createSearchController(Request $request)
    {
        return new SearchController(new ViewFactory, new ContentTypeManager, $request);
    }

    public function createUnknownRouteController(Request $request)
    {
        return new UnknownRouteController(new ViewFactory, new ContentTypeManager, $request);
    }
}
