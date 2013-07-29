<?php

namespace PHPDocSearch\Web\Views\Error;

use \PHPDocSearch\Web\Views\View,
    \PHPDocSearch\Web\TemplateFetcher,
    \PHPDocSearch\Web\Request,
    \PHPDocSearch\Web\ContentNegotiation\ContentType;

class NotFound implements View
{
    private $request;

    private $templateFetcher;

    private $contentType;

    public function __construct(TemplateFetcher $templateFetcher, Request $request, ContentType $contentType = null)
    {
        $this->templateFetcher = $templateFetcher;
        $this->request = $request;
        $this->contentType = $contentType;
    }

    public function render()
    {
        header($this->request->getServerParam('SERVER_PROTOCOL') . ' 404 Not Found');

        switch ($this->contentType->getSubType()) {
            case 'html':
                header('Content-Type: text/html');
                $path = $this->templateFetcher->fetch('error');
                break;

            case 'plain':
                header('Content-Type: text/plain');
                $path = $this->templateFetcher->fetch('error', 'text');
                break;

            default:
                header_remove('Content-Type');
                $path = null;
                break;
        }

        if ($path) {
            $message = 'The requested URI ' . $this->request->getPath() . ' was not found on this server';
            include $path;
        }
    }
}
