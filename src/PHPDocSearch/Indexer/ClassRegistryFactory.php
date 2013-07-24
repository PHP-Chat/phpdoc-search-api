<?php

namespace PHPDocSearch\Indexer;

class ClassRegistryFactory
{
    public function create()
    {
        return new ClassRegistry;
    }
}
