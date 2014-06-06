<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\ConfigOption,
    \PHPDocSearch\Symbols\ConfigOptionFactory;

class ConfigOptionBuilder
{
    /**
     * @var ConfigOptionFactory
     */
    private $configOptionFactory;

    /**
     * Constructor
     *
     * @param ConfigOptionFactory $configOptionFactory
     */
    public function __construct(ConfigOptionFactory $configOptionFactory)
    {
        $this->configOptionFactory = $configOptionFactory;
    }

    /**
     * Build a ConfigOption instance from a DOM element
     *
     * @param \DOMElement $baseEl
     * @param ManualXMLWrapper $xmlWrapper
     * @return ConfigOption|null
     */
    public function build(\DOMElement $baseEl, ManualXMLWrapper $xmlWrapper)
    {
        if (!$parameterEl = $xmlWrapper->getFirst("./db:term/db:parameter", $baseEl)) {
            return null;
        }
        $name = trim($parameterEl->textContent);

        if (!$typeEl = $xmlWrapper->getFirst("./db:term/db:type", $baseEl)) {
            return null;
        }
        $type = trim($typeEl->textContent);

        $slug = $baseEl->getAttribute('xml:id');

        $configOption = $this->configOptionFactory->create();

        $configOption->setName($name);
        $configOption->setType($type);
        $configOption->setSlug($slug);

        return $configOption;
    }
}
