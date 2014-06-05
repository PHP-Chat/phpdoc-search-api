<?php

namespace PHPDocSearch\Symbols;

class ClassMemberFactory
{
    /**
     * Create a new ClassMethod instance
     *
     * @return ClassMethod
     */
    public function createMethod()
    {
        return new ClassMethod;
    }

    /**
     * Create a new ClassProperty instance
     *
     * @return ClassProperty
     */
    public function createProperty()
    {
        return new ClassProperty;
    }

    /**
     * Create a new ClassConstant instance
     *
     * @return ClassConstant
     */
    public function createConstant()
    {
        return new ClassConstant;
    }
}
