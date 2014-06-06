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
    /**
     * Create an IndexController instance
     *
     * @param Request $request
     * @return IndexController
     */
    public function createPageController(Request $request)
    {
        return new IndexController(
            new ViewFetcher,
            new MIMETypeResolver(new MIMETypeBuilder(new MIMETypeFactory)),
            $request
        );
    }

    /**
     * Create a SearchController instance
     *
     * @param Request $request
     * @return SearchController
     */
    public function createSearchController(Request $request)
    {
        return new SearchController(
            new ViewFetcher,
            new MIMETypeResolver(new MIMETypeBuilder(new MIMETypeFactory)),
            new SearchProviderFactory,
            $request
        );
    }

    /**
     * Create an UnknownRouteController instance
     *
     * @param Request $request
     * @return UnknownRouteController
     */
    public function createUnknownRouteController(Request $request)
    {
        return new UnknownRouteController(
            new ViewFetcher,
            new MIMETypeResolver(new MIMETypeBuilder(new MIMETypeFactory)),
            $request
        );
    }
}
