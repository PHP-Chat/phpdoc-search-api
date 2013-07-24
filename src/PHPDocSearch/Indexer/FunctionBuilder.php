<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\FunctionFactory;

class FunctionBuilder
{
    private $functionFactory;

    public function __construct(FunctionFactory $functionFactory)
    {
        $this->functionFactory = $functionFactory;
    }

    public function build(\DOMElement $baseEl, ManualXMLWrapper $xmlWrapper)
    {
        $name = $xmlWrapper->getFirst("./db:refnamediv/db:refname", $baseEl)->textContent;
        $slug = $baseEl->getAttribute('xml:id');

        $function = $this->functionFactory->create();

        $function->setName($name);
        $function->setSlug($slug);

        return $function;
    }
}
