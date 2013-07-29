<?php

namespace PHPDocSearch\Symbols;

abstract class ClassMember extends Symbol
{
    protected $ownerClass;

    protected $memberClass;

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

    public function setMemberClass(GlobalClass $class)
    {
        if ($this->memberClass === null) {
            $this->memberClass = $class;
        }
    }

    public function getMemberClass()
    {
        return $this->memberClass;
    }
}
