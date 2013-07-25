<?php

namespace PHPDocSearch\Web\Views\Error;

class NotFoundHTML
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function render()
    {
        header($this->request->getServerParam('SERVER_PROTOCOL') . ' 404 Not Found');
        header('Content-Type: text/html');
    }
}
