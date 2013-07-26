<?php

namespace PHPDocSearch\Web\Views\Error;

use \PHPDocSearch\Web\Views\View,
    \PHPDocSearch\Web\TemplateFetcher,
    \PHPDocSearch\Web\Request;

class NotAcceptable implements View
{
    private $request;

    private $templateFetcher;

    private $type;

    public function __construct(TemplateFetcher $templateFetcher, Request $request, $type)
    {
        $this->templateFetcher = $templateFetcher;
        $this->request = $request;
        $this->type = $type;
    }

    public function render()
    {
        header($this->request->getServerParam('SERVER_PROTOCOL') . ' 406 Not Acceptable');

        switch ($this->type) {
            case 'html':
                header('Content-Type: text/html');
                $path = $this->templateFetcher->fetch('error');
                break;

            case 'text':
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
