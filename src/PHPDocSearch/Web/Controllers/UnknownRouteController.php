<?php

namespace PHPDocSearch\Web\Controllers;

use PHPDocSearch\Web\Request,
    PHPDocSearch\Web\ViewFactory;

class UnknownRouteController
{
    private $viewFactory;

    private $contentTypeManager;

    private $request;

    public function __construct(ViewFactory $viewFactory, ContentTypeManager $contentTypeManager, Request $request)
    {
        $this->viewFactory = $viewFactory;
        $this->contentTypeManager = $contentTypeManager;
        $this->request = $request;
    }

    public function handleRequest()
    {
        if ($this->contentTypeManager->getResponseType() === 'text/html') {
            $view = $this->viewFactory->createErrorNotFoundHTML($this->request);
        } else {
            $view = $this->viewFactory->createErrorNotFoundText($this->request);
        }

        return $view;
    }
}
