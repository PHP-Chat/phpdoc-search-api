<?php

namespace PHPDocSearch\Symbols;

class FunctionFactory
{
    /**
     * Create a new GlobalFunction instance
     *
     * @return GlobalFunction
     */
    public function create()
    {
        return new GlobalFunction;
    }
}
