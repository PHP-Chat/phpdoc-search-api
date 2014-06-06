<?php

namespace PHPDocSearch\Web;

use \PHPDocSearch\Web\ContentNegotiation\MIMETypeResolver,
    \PHPDocSearch\Web\ContentNegotiation\MIMETypeBuilder,
    \PHPDocSearch\Web\ContentNegotiation\MIMETypeFactory,
    \PHPDocSearch\Web\Search\SearchProviderFactory,
    \PHPDocSearch\Web\Controllers\SearchController,
    \PHPDocSearch\Web\Controllers\IndexController,
    \PHPDocSearch\Web\Controllers\UnknownRouteController;

class ControllerFactory
{
    public function createPageController(Request $request)
    {
        return new IndexController(
            new ViewFetcher,
            new MIMETypeResolver(new MIMETypeBuilder(new MIMETypeFactory)),
            $request
        );
    }

    public function createSearchController(Request $request)
    {
        return new SearchController(
            new ViewFetcher,
            new MIMETypeResolver(new MIMETypeBuilder(new MIMETypeFactory)),
            new SearchProviderFactory,
            $request
        );
    }

    public function createUnknownRouteController(Request $request)
    {
        return new UnknownRouteController(
            new ViewFetcher,
            new MIMETypeResolver(new MIMETypeBuilder(new MIMETypeFactory)),
            $request
        );
    }
}
