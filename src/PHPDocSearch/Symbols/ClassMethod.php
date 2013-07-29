<?php

namespace PHPDocSearch\Symbols;

class ClassMethod extends ClassMember
{
    public function jsonSerialize()
    {
        return (object) [
            'name' => $this->name,
            'class' => $this->memberClass->getName(),
            'defined_in' => $this->ownerClass->getName(),
            'link' => 'http://php.net/' . $this->slug,
        ];
    }
}
