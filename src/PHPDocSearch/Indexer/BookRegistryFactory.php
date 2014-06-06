<?php

namespace PHPDocSearch\Indexer;

class BookRegistryFactory
{
    /**
     * Create a new BookRegistry instance
     *
     * @return BookRegistry
     */
    public function create()
    {
        return new BookRegistry;
    }
}
