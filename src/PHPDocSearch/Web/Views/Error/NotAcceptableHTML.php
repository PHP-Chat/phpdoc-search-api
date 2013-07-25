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
        header($this->request->getServerParam('SERVER_PROTOCOL') . ' 406 Not Acceptable');
        header('Content-Type: text/html');
    }
}
