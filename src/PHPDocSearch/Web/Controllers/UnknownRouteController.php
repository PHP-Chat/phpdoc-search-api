<?php

namespace PHPDocSearch\Web\Controllers;

use \PHPDocSearch\Web\ContentNegotiation\MIMETypeResolver,
    \PHPDocSearch\Web\Request,
    \PHPDocSearch\Web\Views\View,
    \PHPDocSearch\Web\ViewFetcher;

class UnknownRouteController extends Controller
{
    /**
     * @var ViewFetcher
     */
    private $viewFetcher;

    /**
     * @var MIMETypeResolver
     */
    private $mimeTypeResolver;

    /**
     * @var Request
     */
    private $request;

    /**
     * Constructor
     *
     * @param ViewFetcher $viewFetcher
     * @param MIMETypeResolver $mimeTypeResolver
     * @param Request $request
     */
    public function __construct(ViewFetcher $viewFetcher, MIMETypeResolver $mimeTypeResolver, Request $request)
    {
        $this->viewFetcher = $viewFetcher;
        $this->mimeTypeResolver = $mimeTypeResolver;
        $this->request = $request;
    }

    /**
     * Handle a request
     *
     * @return View
     */
    public function handleRequest()
    {
        $acceptTypes = $this->request->getHeader('Accept');
        $availableTypes = ['text/html', 'text/plain'];
        $responseType = $this->mimeTypeResolver->getResponseType($acceptTypes, $availableTypes);

        return $this->viewFetcher->fetch('Error\NotFound', $this->request, $responseType);
    }
}
