<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\GlobalFunction,
    \PHPDocSearch\Symbols\FunctionFactory;

class FunctionBuilder
{
    /**
     * @var FunctionFactory
     */
    private $functionFactory;

    /**
     * Constructor
     *
     * @param FunctionFactory $functionFactory
     */
    public function __construct(FunctionFactory $functionFactory)
    {
        $this->functionFactory = $functionFactory;
    }

    /**
     * Build a GlobalFunction instance from a DOM element
     *
     * @param \DOMElement $baseEl
     * @param ManualXMLWrapper $xmlWrapper
     * @return GlobalFunction|null
     */
    public function build(\DOMElement $baseEl, ManualXMLWrapper $xmlWrapper)
    {
        if (!$refNameEl = $xmlWrapper->getFirst("./db:refnamediv/db:refname", $baseEl)) {
            return null;
        }
        $name = trim($refNameEl->textContent);

        $slug = $baseEl->getAttribute('xml:id');

        $function = $this->functionFactory->create();

        $function->setName($name);
        $function->setSlug($slug);

        return $function;
    }
}
