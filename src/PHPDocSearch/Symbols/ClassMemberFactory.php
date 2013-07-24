<?php

namespace PHPDocSearch\Symbols;

class ClassMemberFactory
{
    public function createMethod()
    {
        return new ClassMethod;
    }

    public function createProperty()
    {
        return new ClassProperty;
    }

    public function createConstant()
    {
        return new ClassConstant;
    }
}
