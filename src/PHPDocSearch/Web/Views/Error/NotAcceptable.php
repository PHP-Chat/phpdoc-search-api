<?php

namespace PHPDocSearch\Web\Views\Error;

use \PHPDocSearch\Web\Views\View,
    \PHPDocSearch\Web\TemplateFetcher,
    \PHPDocSearch\Web\Request,
    \PHPDocSearch\Web\ContentNegotiation\MIMEType;

class NotAcceptable implements View
{
    private $request;

    private $templateFetcher;

    private $contentType;

    public function __construct(TemplateFetcher $templateFetcher, Request $request, MIMEType $contentType = null)
    {
        $this->templateFetcher = $templateFetcher;
        $this->request = $request;
        $this->contentType = $contentType;
    }

    public function render()
    {
        header($this->request->getServerParam('SERVER_PROTOCOL') . ' 406 Not Acceptable');

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
            $message = 'The requested URI ' . $this->request->getPath() . ' is not available in an acceptable format for your user agent';
            include $path;
        }
    }
}
