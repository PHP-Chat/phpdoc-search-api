<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\Book;

class BookRegistry extends SymbolRegistry
{
    /**
     * Add a Book to the registry
     *
     * @param Book $book
     */
    public function register(Book $book)
    {
        if (!$this->isRegistered($this->normalizeName($book->getName()))) {
            $this->add($book);
        }
    }

    /**
     * Get the named Book
     *
     * @param string $name
     * @return Book
     */
    public function getSymbolByName($name)
    {
        return parent::getSymbolByName($name);
    }
}
