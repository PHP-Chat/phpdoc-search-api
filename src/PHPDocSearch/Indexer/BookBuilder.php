<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\BookFactory;

class BookBuilder
{
    private $bookFactory;

    public function __construct(BookFactory $bookFactory)
    {
        $this->bookFactory = $bookFactory;
    }

    public function build(\DOMElement $baseEl, ManualXMLWrapper $xmlWrapper, BookRegistry $bookRegistry)
    {
        $fullName = '';
        if ($title = $xmlWrapper->getFirst('./db:title', $baseEl)) {
            $fullName = trim($title->textContent);
        }

        $shortName = $fullName;
        if ($titleAbbrev = $xmlWrapper->getFirst('./db:titleabbrev', $baseEl)) {
            $shortName = trim($titleAbbrev->textContent);
        }

        $slug = explode('.', $baseEl->getAttribute('xml:id'), 2)[1];

        $book = $this->bookFactory->create();

        $book->setName($fullName);
        $book->setShortName($shortName);
        $book->setSlug($slug);

        $bookRegistry->register($book);

        return $book;
    }
}
