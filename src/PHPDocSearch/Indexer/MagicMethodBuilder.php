<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\MagicMethodFactory;

class MagicMethodBuilder
{
    private $magicMethodFactory;

    public function __construct(MagicMethodFactory $magicMethodFactory)
    {
        $this->magicMethodFactory = $magicMethodFactory;
    }

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
