<?php

namespace PHPDocSearch\Symbols;

class ConstantFactory
{
    /**
     * Create a new GlobalConstant instance
     *
     * @return GlobalConstant
     */
    public function create()
    {
        return new GlobalConstant;
    }
}
