<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\ControlStructureFactory;

class ControlStructureBuilder
{
    private $controlStructureFactory;

    public function __construct(ControlStructureFactory $controlStructureFactory)
    {
        $this->controlStructureFactory = $controlStructureFactory;
    }

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
