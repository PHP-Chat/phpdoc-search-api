<?php

namespace PHPDocSearch\Web\Views\Error;

use \PHPDocSearch\Web\Views\View;

class BadRequest extends View
{
    /**
     * Output the content of this view
     */
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
            /** @noinspection PhpUnusedLocalVariableInspection */
            $message = 'The request was malformed or incomplete';
            /** @noinspection PhpIncludeInspection */
            include $path;
        }
    }
}
