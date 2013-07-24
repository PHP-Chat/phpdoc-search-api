<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\ConstantFactory;

class ConstantBuilder
{
    private $constantFactory;

    private $xpath;

    public function __construct(ConstantFactory $constantFactory, ManualXMLWrapper $xpath)
    {
        $this->constantFactory = $constantFactory;
        $this->xpath = $xpath;
    }

    public function build(\DOMElement $baseEl)
    {
        $name = '';
        if ($constantEl = $this->xpath->getFirst("./db:term/db:constant", $baseEl)) {
            $name = trim($constantEl->textContent);
        }

        $type = '';
        if ($typeEl = $this->xpath->getFirst("./db:term/db:type", $baseEl)) {
            $type = trim($typeEl->textContent);
        }

        $slug = $baseEl->getAttribute('xml:id');

        $constant = $this->constantFactory->create();

        $constant->setName($name);
        $constant->setType($type);
        $constant->setSlug($slug);

        return $constant;
    }
}
