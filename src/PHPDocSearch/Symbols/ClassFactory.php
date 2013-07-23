<?php

namespace PHPDocSearch\Symbols;

class ClassFactory
{
    public function create()
    {
        return new GlobalClass;
    }
}
