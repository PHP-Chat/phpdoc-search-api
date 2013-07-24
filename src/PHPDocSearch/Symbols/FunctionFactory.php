<?php

namespace PHPDocSearch\Symbols;

class FunctionFactory
{
    public function create()
    {
        return new GlobalFunction;
    }
}
