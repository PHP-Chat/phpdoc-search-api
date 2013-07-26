<?php

namespace PHPDocSearch\Web\Controllers;

use \PHPDocSearch\Web\ContentNegotiation\ContentTypeResolver,
    \PHPDocSearch\Web\Search\SearchProviderFactory,
    \PHPDocSearch\Web\Request,
    \PHPDocSearch\Web\ViewFetcher;

class SearchController
{
    private $viewFetcher;

    private $contentTypeResolver;

    private $searchProviderFactory;

    private $request;

    public function __construct(
        ViewFetcher $viewFetcher,
        ContentTypeResolver $contentTypeResolver,
        SearchProviderFactory $searchProviderFactory,
        Request $request
    ) {
        $this->viewFetcher = $viewFetcher;
        $this->contentTypeResolver = $contentTypeResolver;
        $this->searchProviderFactory = $searchProviderFactory;
        $this->request = $request;
    }

    public function handleRequest()
    {
        $acceptTypes = $request->getHeader('Accept');
        $availableTypes = ['application/json', 'text/json', 'application/xml', 'text/xml'];
        $responseType = $this->contentTypeResolver->getResponseType($acceptTypes, $availableTypes);

        $type = null;

        if ($responseType) {
            $searchProvider = $this->searchProviderFactory->create($this->request);

            $type = explode('/', $responseType, 2)[1];
            $view = $this->viewFetcher->fetch('Search', $this->request, $type, $this->searchProvider);
        } else {
            $availableTypes = ['text/html', 'text/plain'];
            $responseType = $this->contentTypeResolver->getResponseType($acceptTypes, $availableTypes);

            switch ($responseType) {
                case 'text/html':
                    $type = 'html';
                    break;

                case 'text/plain':
                    $type = 'text';
                    break;
            }

            $view = $this->viewFetcher->fetch('Error\NotAcceptable', $this->request, $type);
        }

        return $view;
    }
}
