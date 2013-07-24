<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\ConstantFactory;

class ConstantBuilder
{
    private $constantFactory;

    public function __construct(ConstantFactory $constantFactory)
    {
        $this->constantFactory = $constantFactory;
    }

    public function build(\DOMElement $baseEl, ManualXMLWrapper $xmlWrapper)
    {
        $name = '';
        if ($constantEl = $xmlWrapper->getFirst("./db:term/db:constant", $baseEl)) {
            $name = trim($constantEl->textContent);
        }

        $type = '';
        if ($typeEl = $xmlWrapper->getFirst("./db:term/db:type", $baseEl)) {
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
