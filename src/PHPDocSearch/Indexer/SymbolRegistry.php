<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\Symbol;

abstract class SymbolRegistry implements \Iterator, \Countable
{
    /**
     * Array of registered Symbol objects
     *
     * @var array
     */
    protected $symbols = [];

    /**
     * Number of registered symbols
     *
     * @var int
     */
    private $length = 0;

    /**
     * Whether the iteration pointer is valid
     *
     * @var bool
     */
    private $hasNext = false;

    /**
     * Add a new symbol to the registry
     *
     * @param Symbol $symbol
     */
    protected function add(Symbol $symbol)
    {
        $this->symbols[$this->normalizeName($symbol->getName())] = $symbol;

        $this->length++;
    }

    /**
     * Normalise the name of a symbol for use as a storage key
     *
     * @param string $name
     * @return string
     */
    protected function normalizeName($name)
    {
        return trim(strtolower($name));
    }

    /**
     * Get the value to which the iteration pointer currently points
     *
     * @return Symbol
     */
    public function current()
    {
        return current($this->symbols);
    }

    /**
     * Get the key to which the iteration pointer currently points
     *
     * @return string
     */
    public function key()
    {
        return key($this->symbols);
    }

    /**
     * Advance the iteration pointer
     */
    public function next()
    {
        $this->hasNext = next($this->symbols) !== false;
    }

    /**
     * Reset the iteration pointer
     */
    public function rewind()
    {
        reset($this->symbols);
        $this->hasNext = (bool) $this->length;
    }

    /**
     * Determine whether the iteration pointer is valid
     *
     * @return bool
     */
    public function valid()
    {
        return $this->hasNext;
    }

    /**
     * Get the number of registered symbols
     *
     * @return int
     */
    public function count()
    {
        return $this->length;
    }

    /**
     * Determine whether the named symbol is registered
     *
     * @param string $name
     * @return bool
     */
    public function isRegistered($name)
    {
        return isset($this->symbols[$this->normalizeName($name)]);
    }

    /**
     * Get the named symbol
     *
     * @param string $name
     * @return Symbol
     */
    public function getSymbolByName($name)
    {
        $name = $this->normalizeName($name);

        return isset($this->symbols[$name]) ? $this->symbols[$name] : null;
    }
}
