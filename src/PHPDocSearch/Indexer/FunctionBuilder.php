<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\FunctionFactory;

class FunctionBuilder
{
    private $functionFactory;

    private $xpath;

    public function __construct(FunctionFactory $functionFactory, ManualXPath $xpath)
    {
        $this->functionFactory = $functionFactory;
        $this->xpath = $xpath;
    }

    public function build(\DOMElement $baseEl)
    {
        $name = $this->xpath->getFirst("./db:refnamediv/db:refname", $func)->textContent;
        $slug = $func->getAttribute('xml:id');

        $function = $this->functionFactory->create();

        $function->setName($name);
        $function->setSlug($slug);

        return $function;
    }
}
