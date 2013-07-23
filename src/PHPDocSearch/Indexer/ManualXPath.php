<?php

namespace PHPDocSearch\Indexer;

class ManualXPath
{
    private $xpath;

    public function __construct(\DOMXPath $xpath)
    {
        $xpath->registerNamespace('db', 'http://docbook.org/ns/docbook');
        $xpath->registerNamespace('pd', 'http://php.net/ns/phpdoc');
        $xpath->registerNamespace('xml', 'http://www.w3.org/XML/1998/namespace');

        $this->xpath = $xpath;
    }

    public function query($query, $baseEl = null)
    {
        return $this->xpath->query($query, $baseEl);
    }

    public function getFirst($query, $baseEl = null)
    {
        $result = $this->xpath->query($query, $baseEl);

        if ($result->length) {
            return $result->item(0);
        }
    }
}
