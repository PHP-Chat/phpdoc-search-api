<?php

namespace PHPDocSearch\Symbols;

class ClassConstant extends ClassMember
{
    public function jsonSerialize()
    {
        return (object) [
            'name' => $this->name,
            'class' => $this->memberClass->getName(),
            'defined_in' => $this->ownerClass->getName(),
            'link' => 'http://php.net/' . $this->ownerClass->getSlug() . '#' . $this->slug,
        ];
    }
}
