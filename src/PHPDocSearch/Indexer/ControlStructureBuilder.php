<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\ControlStructure,
    \PHPDocSearch\Symbols\ControlStructureFactory;

class ControlStructureBuilder
{
    /**
     * @var ControlStructureFactory
     */
    private $controlStructureFactory;

    /**
     * Constructor
     *
     * @param ControlStructureFactory $controlStructureFactory
     */
    public function __construct(ControlStructureFactory $controlStructureFactory)
    {
        $this->controlStructureFactory = $controlStructureFactory;
    }

    /**
     * Build a ControlStructure instance from a DOM element
     *
     * @param \DOMElement $baseEl
     * @param ManualXMLWrapper $xmlWrapper
     * @return ControlStructure|null
     */
    public function build(\DOMElement $baseEl, ManualXMLWrapper $xmlWrapper)
    {
        if (!$titleEl = $xmlWrapper->getFirst("./db:title", $baseEl)) {
            return null;
        }
        $name = trim($titleEl->textContent);

        if (!$xmlWrapper->getFirst(".//db:literal[.='$name']", $baseEl)) {
            return null;
        }

        $slug = $baseEl->getAttribute('xml:id');

        $controlStructure = $this->controlStructureFactory->create();

        $controlStructure->setName($name);
        $controlStructure->setSlug($slug);

        return $controlStructure;
    }
}
