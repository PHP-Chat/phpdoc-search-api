<?php

namespace PHPDocSearch\Web;

use PHPDocSearch\Web\Views;

class ViewFactory
{
    public function createErrorNotFoundHTML()
    {
        return new Views\Error\NotFoundHTML(new TemplateFetcher);
    }

    public function createErrorNotAcceptableHTML()
    {
        return new Views\Error\NotAcceptableHTML(new TemplateFetcher);
    }
}
