<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\MagicMethod,
    \PHPDocSearch\Symbols\MagicMethodFactory;

class MagicMethodBuilder
{
    /**
     * @var MagicMethodFactory
     */
    private $magicMethodFactory;

    /**
     * Constructor
     *
     * @param MagicMethodFactory $magicMethodFactory
     */
    public function __construct(MagicMethodFactory $magicMethodFactory)
    {
        $this->magicMethodFactory = $magicMethodFactory;
    }

    /**
     * Build a MagicMethod instance from a DOM element
     *
     * @param \DOMElement $baseEl
     * @param ManualXMLWrapper $xmlWrapper
     * @return MagicMethod|null
     */
    public function build(\DOMElement $baseEl, ManualXMLWrapper $xmlWrapper)
    {
        if (!$methodNameEl = $xmlWrapper->getFirst(".//db:methodname", $baseEl)) {
            return null;
        }
        $name = trim($methodNameEl->textContent);

        $currentEl = $baseEl;
        while ($currentEl = $currentEl->parentNode) {
            if ($currentEl->tagName === 'sect1') {
                $slug = $currentEl->getAttribute('xml:id') . '#' . $baseEl->getAttribute('xml:id');
                break;
            }
        }
        if (!isset($slug)) {
            return null;
        }

        $magicMethod = $this->magicMethodFactory->create();

        $magicMethod->setName($name);
        $magicMethod->setSlug($slug);

        return $magicMethod;
    }
}
