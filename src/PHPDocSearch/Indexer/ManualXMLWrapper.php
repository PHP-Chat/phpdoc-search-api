<?php

namespace PHPDocSearch\Indexer;

class ManualXMLWrapper
{
    private $docPath;

    private $keepFile;

    private $doc;

    private $xpath;

    public function __construct($docPath, $keepFile)
    {
        $this->docPath = $docPath;
        $this->keepFile = $keepFile;

        $this->loadDocument();
    }

    private function loadDocument()
    {
        $this->doc = new \DOMDocument;
        if (!$this->doc->load($this->docPath)) {
            throw new \RuntimeException('Loading manual XML document failed');
        }

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

        if (!$this->keepFile) {
            unlink($this->docPath);
        }
    }
}
