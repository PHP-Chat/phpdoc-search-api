<?php

namespace PHPDocSearch\Web\Controllers;

use PHPDocSearch\Web\ContentNegotiation\MIMETypeResolver,
    PHPDocSearch\Web\Request,
    PHPDocSearch\Web\ViewFetcher;

class IndexController
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
        $availableTypes = ['text/html'];
        $responseType = $this->mimeTypeResolver->getResponseType($acceptTypes, $availableTypes);

        if ($responseType) {
            // do something here
        } else {
            $availableTypes = ['text/plain'];
            $responseType = $this->mimeTypeResolver->getResponseType($acceptTypes, $availableTypes);

            $view = $this->viewFetcher->fetch('Error\NotAcceptable', $this->request, $responseType);
        }

        return $view;
    }
}
