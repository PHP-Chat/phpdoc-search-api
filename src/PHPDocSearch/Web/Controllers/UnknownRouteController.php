<?php

namespace PHPDocSearch\Web\Controllers;

use \PHPDocSearch\Web\ContentNegotiation\MIMETypeResolver,
    \PHPDocSearch\Web\Request,
    \PHPDocSearch\Web\ViewFetcher;

class UnknownRouteController
{
    private $viewFetcher;

    private $mimeTypeResolver;

    private $request;

    public function __construct(ViewFetcher $viewFetcher, MIMETypeResolver $mimeTypeResolver, Request $request)
    {
        $this->viewFetcher = $viewFetcher;
        $this->mimeTypeResolver = $mimeTypeResolver;
        $this->request = $request;
    }

    public function handleRequest()
    {
        $acceptTypes = $this->request->getHeader('Accept');
        $availableTypes = ['text/html', 'text/plain'];
        $responseType = $this->mimeTypeResolver->getResponseType($acceptTypes, $availableTypes);

        return $this->viewFetcher->fetch('Error\NotFound', $this->request, $responseType);
    }
}
