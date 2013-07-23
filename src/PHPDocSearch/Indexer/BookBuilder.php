<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\BookFactory;

class BookBuilder
{
    private $bookRegistry;

    private $bookFactory;

    private $xpath;

    public function __construct(BookRegistry $bookRegistry, BookFactory $bookFactory, ManualXPath $xpath)
    {
        $this->bookRegistry = $bookRegistry;
        $this->bookFactory = $bookFactory;
        $this->xpath = $xpath;
    }

    public function build(\DOMElement $baseEl)
    {
        $fullName = '';
        if ($title = $this->xpath->getFirst('./db:title', $baseEl)) {
            $fullName = trim($title->textContent);
        }

        $shortName = $fullName;
        if ($titleAbbrev = $this->xpath->getFirst('./db:titleabbrev', $baseEl)) {
            $shortName = trim($titleAbbrev->textContent);
        }

        $slug = explode('.', $baseEl->getAttribute('xml:id'), 2)[1];

        $book = $this->bookFactory->create();

        $book->setName($fullName);
        $book->setShortName($shortName);
        $book->setSlug($slug);

        $this->bookRegistry->register($book);

        return $book;
    }
}
