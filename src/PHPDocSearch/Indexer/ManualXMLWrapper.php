<?php

namespace PHPDocSearch\Indexer;

class ManualXMLWrapper
{
    /**
     * The path to the wrapped document on disk
     *
     * @var string
     */
    private $docPath;

    /**
     * Whether to delete the document from disk on close
     *
     * @var bool
     */
    private $keepFile;

    /**
     * The wrapped DOMDocument instance
     *
     * @var \DOMDocument
     */
    private $doc;

    /**
     * The wrapped DOMDocument instance
     *
     * @var \DOMXPath
     */
    private $xpath;

    /**
     * Constructor
     *
     * @param string $docPath
     * @param bool $keepFile
     */
    public function __construct($docPath, $keepFile)
    {
        $this->docPath = (string) $docPath;
        $this->keepFile = (bool) $keepFile;

        $this->loadDocument();
    }

    /**
     * Load the document from disk
     *
     * @throws \RuntimeException
     */
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

    /**
     * Run an XPath query and return the matched nodes
     *
     * @param string $query
     * @param \DOMNode $baseEl
     * @return \DOMNodeList
     */
    public function query($query, \DOMNode $baseEl = null)
    {
        return $this->xpath->query($query, $baseEl);
    }

    /**
     * Run an XPath query and return the first matched element
     * @param string $query
     * @param \DOMNode $baseEl
     * @return \DOMNode|null
     */
    public function getFirst($query, \DOMNode $baseEl = null)
    {
        $result = $this->xpath->query($query, $baseEl);

        return $result->length ? $result->item(0) : null;
    }

    /**
     * Close the wrapped document and attempt to free up used memory
     */
    public function close()
    {
        $this->doc = $this->xpath = null;
        gc_collect_cycles();

        if (!$this->keepFile) {
            unlink($this->docPath);
        }
    }
}
