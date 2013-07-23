<?php

namespace PHPDocSearch\Symbols;

class ConstantFactory
{
    public function create()
    {
        return new GlobalConstant;
    }
}
