<?php

namespace PHPDocSearch\Symbols;

class BookFactory
{
    /**
     * Create a new Book instance
     *
     * @return Book
     */
    public function create()
    {
        return new Book;
    }
}
