<?php

namespace PHPDocSearch\Symbols;

abstract class GlobalSymbol extends Symbol
{
    /**
     * The book that defines this symbol
     *
     * @var Book
     */
    protected $book;

    /**
     * Set the book that defines this symbol
     *
     * @param Book $book
     */
    public function setBook(Book $book)
    {
        $this->book = $book;
    }

    /**
     * Get the book that defines this symbol
     *
     * @return Book
     */
    public function getBook()
    {
        return $this->book;
    }
}
