<?php

namespace PHPDocSearch\Web\Search;

class Query
{
    const ENTITY_BOOK             = 0b0000000001;
    const ENTITY_CLASS            = 0b0000000010;
    const ENTITY_CLASSPROPERTY    = 0b0000000100;
    const ENTITY_CLASSMETHOD      = 0b0000001000;
    const ENTITY_CLASSCONSTANT    = 0b0000010000;
    const ENTITY_CONFIGOPTION     = 0b0000100000;
    const ENTITY_CONSTANT         = 0b0001000000;
    const ENTITY_CONTROLSTRUCTURE = 0b0010000000;
    const ENTITY_FUNCTION         = 0b0100000000;
    const ENTITY_MAGICMETHOD      = 0b1000000000;

    const ENTITY_SINGLE_KEYWORD   = 0b1111100011;
    const ENTITY_DOUBLE_KEYWORD   = 0b0000111100;

    private $elements;

    private $flags;

    private $signature;

    public function __construct(array $elements, $signature, $flags)
    {
        $this->elements = $elements;
        $this->signature = $signature;
        $this->flags = $flags;
    }

    public function getSignature()
    {
        return $this->signature;
    }

    public function getElement($index)
    {
        return isset($this->elements[$index]) ? $this->elements[$index] : null;
    }

    public function getElements()
    {
        return $this->elements;
    }

    public function searchBooks()
    {
        return (bool) ($this->flags & self::ENTITY_BOOK);
    }

    public function searchClasses()
    {
        return (bool) ($this->flags & self::ENTITY_CLASS);
    }

    public function searchClassProperties()
    {
        return (bool) ($this->flags & self::ENTITY_CLASSPROPERTY);
    }

    public function searchClassMethods()
    {
        return (bool) ($this->flags & self::ENTITY_CLASSMETHOD);
    }

    public function searchClassConstants()
    {
        return (bool) ($this->flags & self::ENTITY_CLASSCONSTANT);
    }

    public function searchConfigOptions()
    {
        return (bool) ($this->flags & self::ENTITY_CONFIGOPTION);
    }

    public function searchConstants()
    {
        return (bool) ($this->flags & self::ENTITY_CONSTANT);
    }

    public function searchControlStructures()
    {
        return (bool) ($this->flags & self::ENTITY_CONTROLSTRUCTURE);
    }

    public function searchFunctions()
    {
        return (bool) ($this->flags & self::ENTITY_FUNCTION);
    }

    public function searchMagicMethods()
    {
        return (bool) ($this->flags & self::ENTITY_MAGICMETHOD);
    }
}
