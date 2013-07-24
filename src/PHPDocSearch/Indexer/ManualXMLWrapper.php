<?php

namespace PHPDocSearch\Indexer;

class ManualXMLWrapper
{
    private $doc;

    private $xpath;

    public function __construct($docPath)
    {
        $this->doc = new \DOMDocument;
        $this->doc->load($docPath);

        $this->xpath = new \DOMXPath($this->doc);
        $this->xpath->registerNamespace('db', 'http://docbook.org/ns/docbook');
        $this->xpath->registerNamespace('pd', 'http://php.net/ns/phpdoc');
        $this->xpath->registerNamespace('xml', 'http://www.w3.org/XML/1998/namespace');
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

    public function close()
    {
        $this->doc = $this->xpath = null;
        gc_collect_cycles();
    }
}
