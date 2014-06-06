<?php

namespace PHPDocSearch\Web\Controllers;

use \PHPDocSearch\Web\ContentNegotiation\MIMETypeResolver,
    \PHPDocSearch\Web\Search\SearchProviderFactory,
    \PHPDocSearch\Web\Request,
    \PHPDocSearch\Web\ViewFetcher;

class SearchController
{
    private $viewFetcher;

    private $mimeTypeResolver;

    private $searchProviderFactory;

    private $request;

    public function __construct(
        ViewFetcher $viewFetcher,
        MIMETypeResolver $mimeTypeResolver,
        SearchProviderFactory $searchProviderFactory,
        Request $request
    ) {
        $this->viewFetcher = $viewFetcher;
        $this->mimeTypeResolver = $mimeTypeResolver;
        $this->searchProviderFactory = $searchProviderFactory;
        $this->request = $request;
    }

    public function handleRequest()
    {
        $acceptTypes = $this->request->getHeader('Accept');
        $availableTypes = ['application/json', 'text/json'];//, 'application/xml', 'text/xml'];
        $responseType = $this->mimeTypeResolver->getResponseType($acceptTypes, $availableTypes);

        if ($responseType) {
            if ($this->request->hasArg('q')) {
                $searchProvider = $this->searchProviderFactory->create($this->request);

                $view = $this->viewFetcher->fetch('Search', $this->request, $responseType, $searchProvider);
            } else {
                $availableTypes = ['text/html', 'text/plain'];
                $responseType = $this->mimeTypeResolver->getResponseType($acceptTypes, $availableTypes);

                $view = $this->viewFetcher->fetch('Error\BadRequest', $this->request, $responseType);
            }
        } else {
            $availableTypes = ['text/html', 'text/plain'];
            $responseType = $this->mimeTypeResolver->getResponseType($acceptTypes, $availableTypes);

            $view = $this->viewFetcher->fetch('Error\NotAcceptable', $this->request, $responseType);
        }

        return $view;
    }
}
