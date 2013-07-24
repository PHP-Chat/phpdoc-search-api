<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\ConfigOptionFactory;

class ConfigOptionBuilder
{
    private $configOptionFactory;

    private $xpath;

    public function __construct(ConfigOptionFactory $configOptionFactory, ManualXMLWrapper $xpath)
    {
        $this->configOptionFactory = $configOptionFactory;
        $this->xpath = $xpath;
    }

    public function build(\DOMElement $baseEl)
    {
        $name = '';
        if ($parameterEl = $this->xpath->getFirst("./db:term/db:parameter", $baseEl)) {
            $name = trim($parameterEl->textContent);
        }

        $type = '';
        if ($typeEl = $this->xpath->getFirst("./db:term/db:type", $baseEl)) {
            $type = trim($typeEl->textContent);
        }

        $slug = $baseEl->getAttribute('xml:id');

        $configOption = $this->configOptionFactory->create();

        $configOption->setName($name);
        $configOption->setType($type);
        $configOption->setSlug($slug);

        return $configOption;
    }
}
