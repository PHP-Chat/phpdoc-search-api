<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\GlobalConstant,
    \PHPDocSearch\Symbols\ConstantFactory;

class ConstantBuilder
{
    /**
     * @var ConstantFactory
     */
    private $constantFactory;

    /**
     * Constructor
     *
     * @param ConstantFactory $constantFactory
     */
    public function __construct(ConstantFactory $constantFactory)
    {
        $this->constantFactory = $constantFactory;
    }

    /**
     * Build a GlobalConstant instance from a DOM element
     *
     * @param \DOMElement $baseEl
     * @param ManualXMLWrapper $xmlWrapper
     * @return GlobalConstant|null
     */
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
