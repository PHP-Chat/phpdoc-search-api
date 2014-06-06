<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\Book,
    \PHPDocSearch\Symbols\BookFactory;

class BookBuilder
{
    /**
     * Factory which makes Book objects
     *
     * @var BookFactory
     */
    private $bookFactory;

    /**
     * Constructor
     *
     * @param BookFactory $bookFactory
     */
    public function __construct(BookFactory $bookFactory)
    {
        $this->bookFactory = $bookFactory;
    }

    /**
     * Build a Book instance from a DOM element
     *
     * @param \DOMElement $baseEl
     * @param ManualXMLWrapper $xmlWrapper
     * @param BookRegistry $bookRegistry
     * @return Book|null
     */
    public function build(\DOMElement $baseEl, ManualXMLWrapper $xmlWrapper, BookRegistry $bookRegistry)
    {
        if (!$titleEl = $xmlWrapper->getFirst('./db:title', $baseEl)) {
            return null;
        }
        $fullName = trim($titleEl->textContent);

        if ($titleAbbrev = $xmlWrapper->getFirst('./db:titleabbrev', $baseEl)) {
            $shortName = trim($titleAbbrev->textContent);
        } else {
            $shortName = $fullName;
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
