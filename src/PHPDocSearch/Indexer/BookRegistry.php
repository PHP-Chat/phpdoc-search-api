<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\Book;

class BookRegistry extends SymbolRegistry
{
    private $books = [];

    public function register(Book $book)
    {
        if (!$this->isRegistered($this->normalizeName($book->getName()))) {
            $this->add($book);
        }
    }
}
