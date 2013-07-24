<?php

namespace PHPDocSearch\Symbols;

abstract class GlobalSymbol extends Symbol
{
    protected $book;

    public function setBook(Book $book)
    {
        $this->book = $book;
    }

    public function getBook()
    {
        return $this->book;
    }
}
