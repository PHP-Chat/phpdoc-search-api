<?php

namespace PHPDocSearch\Web\Controllers;

use \PHPDocSearch\Web\Views\View;

abstract class Controller
{
    /**
     * Handle a request
     *
     * @return View
     */
    abstract public function handleRequest();
}
