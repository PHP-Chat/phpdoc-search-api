<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\Symbol;

abstract class SymbolRegistry implements \Iterator, \Countable
{
    protected $symbols = [];

    private $length;

    private $hasNext;

    protected function add(Symbol $symbol)
    {
        $this->symbols[$this->normalizeName($symbol->getName())] = $symbol;

        $this->length++;
    }

    protected function normalizeName($name)
    {
        return trim(strtolower($name));
    }

    public function current()
    {
        return current($this->symbols);
    }

    public function key()
    {
        return key($this->symbols);
    }

    public function next()
    {
        $this->hasNext = next($this->symbols) !== false;
    }

    public function rewind()
    {
        reset($this->symbols);
        $this->hasNext = (bool) $this->length;
    }

    public function valid()
    {
        return $this->hasNext;
    }

    public function count()
    {
        return $this->length;
    }

    public function isRegistered($name)
    {
        return isset($this->symbols[$this->normalizeName($name)]);
    }

    public function getSymbolByName($name)
    {
        $name = $this->normalizeName($name);

        return isset($this->symbols[$name]) ? $this->symbols[$name] : null;
    }
}
