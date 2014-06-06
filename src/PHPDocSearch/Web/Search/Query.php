<?php

namespace PHPDocSearch\Web\Search;

class Query
{
    /**
     * Entity type constants
     */
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

    /**
     * Entity group aliases
     */
    const ENTITY_SINGLE_KEYWORD   = 0b1111100011;
    const ENTITY_DOUBLE_KEYWORD   = 0b0000111100;

    /**
     * @var string[]
     */
    private $elements;

    /**
     * @var int
     */
    private $flags;

    /**
     * @var string
     */
    private $signature;

    /**
     * Constructor
     *
     * @param string[] $elements
     * @param string $signature
     * @param int $flags
     */
    public function __construct(array $elements, $signature, $flags)
    {
        $this->elements = $elements;
        $this->signature = $signature;
        $this->flags = $flags;
    }

    /**
     * Get the query signature
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Get the query element at the specified index
     *
     * @param $index
     * @return null|string
     */
    public function getElement($index)
    {
        return isset($this->elements[$index]) ? $this->elements[$index] : null;
    }

    /**
     * Get and array of all of the query elements
     *
     * @return string[]
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Determine whether the query will search for matching books
     *
     * @return bool
     */
    public function willSearchBooks()
    {
        return (bool) ($this->flags & self::ENTITY_BOOK);
    }

    /**
     * Determine whether the query will search for matching classes
     *
     * @return bool
     */
    public function willSearchClasses()
    {
        return (bool) ($this->flags & self::ENTITY_CLASS);
    }

    /**
     * Determine whether the query will search for matching class properties
     *
     * @return bool
     */
    public function willSearchClassProperties()
    {
        return (bool) ($this->flags & self::ENTITY_CLASSPROPERTY);
    }

    /**
     * Determine whether the query will search for matching class methods
     *
     * @return bool
     */
    public function willSearchClassMethods()
    {
        return (bool) ($this->flags & self::ENTITY_CLASSMETHOD);
    }

    /**
     * Determine whether the query will search for matching class constants
     *
     * @return bool
     */
    public function willSearchClassConstants()
    {
        return (bool) ($this->flags & self::ENTITY_CLASSCONSTANT);
    }

    /**
     * Determine whether the query will search for matching config options
     *
     * @return bool
     */
    public function willSearchConfigOptions()
    {
        return (bool) ($this->flags & self::ENTITY_CONFIGOPTION);
    }

    /**
     * Determine whether the query will search for matching constants
     *
     * @return bool
     */
    public function willSearchConstants()
    {
        return (bool) ($this->flags & self::ENTITY_CONSTANT);
    }

    /**
     * Determine whether the query will search for matching control structures
     *
     * @return bool
     */
    public function willSearchControlStructures()
    {
        return (bool) ($this->flags & self::ENTITY_CONTROLSTRUCTURE);
    }

    /**
     * Determine whether the query will search for matching functions
     *
     * @return bool
     */
    public function willSearchFunctions()
    {
        return (bool) ($this->flags & self::ENTITY_FUNCTION);
    }

    /**
     * Determine whether the query will search for matching magic methods
     *
     * @return bool
     */
    public function willSearchMagicMethods()
    {
        return (bool) ($this->flags & self::ENTITY_MAGICMETHOD);
    }
}
