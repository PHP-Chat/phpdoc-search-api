<?php

namespace PHPDocSearch\Web;

use \PHPDocSearch\Web\ContentNegotiation\ContentTypeResolver,
    \PHPDocSearch\Web\ContentNegotiation\ContentTypeBuilder,
    \PHPDocSearch\Web\ContentNegotiation\ContentTypeFactory,
    \PHPDocSearch\Web\Search\SearchProviderFactory,
    \PHPDocSearch\Web\Controllers\SearchController,
    \PHPDocSearch\Web\Controllers\IndexController,
    \PHPDocSearch\Web\Controllers\UnknownRouteController;

class ControllerFactory
{
    public function createPageController(Request $request)
    {
        return new PageController(
            new ViewFetcher,
            new ContentTypeResolver(new ContentTypeBuilder(new ContentTypeFactory)),
            $request
        );
    }

    public function createSearchController(Request $request)
    {
        return new SearchController(
            new ViewFetcher,
            new ContentTypeResolver(new ContentTypeBuilder(new ContentTypeFactory)),
            new SearchProviderFactory,
            $request
        );
    }

    public function createUnknownRouteController(Request $request)
    {
        return new UnknownRouteController(
            new ViewFetcher,
            new ContentTypeResolver(new ContentTypeBuilder(new ContentTypeFactory)),
            $request
        );
    }
}
