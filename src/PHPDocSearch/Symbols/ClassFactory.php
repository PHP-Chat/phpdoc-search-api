<?php

namespace PHPDocSearch\Symbols;

class ClassFactory
{
    /**
     * Create a new GlobalClass instance
     *
     * @return GlobalClass
     */
    public function create()
    {
        return new GlobalClass;
    }
}
