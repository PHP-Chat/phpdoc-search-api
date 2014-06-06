<?php

namespace PHPDocSearch\Web\Views\Error;

use \PHPDocSearch\Web\Views\View;

class NotAcceptable extends View
{
    /**
     * Output the content of this view
     */
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
            /** @noinspection PhpUnusedLocalVariableInspection */
            $message = 'The requested URI ' . $this->request->getPath() . ' is not available in an acceptable format for your user agent';
            /** @noinspection PhpIncludeInspection */
            include $path;
        }
    }
}
