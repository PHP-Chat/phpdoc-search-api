<?php

namespace PHPDocSearch\Indexer;

class ClassRegistryFactory
{
    /**
     * Create a new ClassRegistry instance
     *
     * @return ClassRegistry
     */
    public function create()
    {
        return new ClassRegistry;
    }
}
