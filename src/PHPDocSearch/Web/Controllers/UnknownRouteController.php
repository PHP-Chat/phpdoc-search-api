<?php

namespace PHPDocSearch\Web\Controllers;

use \PHPDocSearch\Web\ContentNegotiation\ContentTypeResolver,
    \PHPDocSearch\Web\Request,
    \PHPDocSearch\Web\ViewFetcher;

class UnknownRouteController
{
    private $viewFetcher;

    private $contentTypeResolver;

    private $request;

    public function __construct(ViewFetcher $viewFetcher, ContentTypeResolver $contentTypeResolver, Request $request)
    {
        $this->viewFetcher = $viewFetcher;
        $this->contentTypeResolver = $contentTypeResolver;
        $this->request = $request;
    }

    public function handleRequest()
    {
        $acceptTypes = $this->request->getHeader('Accept');
        $availableTypes = ['text/html', 'text/plain'];
        $responseType = $this->contentTypeResolver->getResponseType($acceptTypes, $availableTypes);

        return $this->viewFetcher->fetch('Error\NotFound', $this->request, $responseType);
    }
}
