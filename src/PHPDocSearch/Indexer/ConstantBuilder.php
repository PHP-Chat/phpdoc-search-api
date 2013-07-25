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
        if (!$constantEl = $xmlWrapper->getFirst("./db:term/db:constant", $baseEl)) {
            return null;
        }
        $name = trim($constantEl->textContent);

        if (!$typeEl = $xmlWrapper->getFirst("./db:term/db:type", $baseEl)) {
            return null;
        }
        $type = trim($typeEl->textContent);

        $slug = $baseEl->getAttribute('xml:id');

        $constant = $this->constantFactory->create();

        $constant->setName($name);
        $constant->setType($type);
        $constant->setSlug($slug);

        return $constant;
    }
}
