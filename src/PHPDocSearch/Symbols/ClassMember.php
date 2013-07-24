<?php

namespace PHPDocSearch\Symbols;

abstract class ClassMember extends Symbol
{
    private $ownerClass;

    public function setOwnerClass(GlobalClass $class)
    {
        if ($this->ownerClass === null) {
            $this->ownerClass = $class;
        }
    }

    public function getOwnerClass()
    {
        return $this->ownerClass;
    }
}
