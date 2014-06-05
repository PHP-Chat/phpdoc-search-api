<?php

namespace PHPDocSearch\Symbols;

class MagicMethodFactory
{
    /**
     * Create a new MagicMethod instance
     *
     * @return MagicMethod
     */
    public function create()
    {
        return new MagicMethod;
    }
}
