<?php

namespace PHPDocSearch\Indexer;

class BookRegistryFactory
{
    public function create()
    {
        return new BookRegistry;
    }
}
