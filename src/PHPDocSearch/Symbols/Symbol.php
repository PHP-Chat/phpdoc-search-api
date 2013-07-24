<?php

namespace PHPDocSearch\Symbols;

abstract class Symbol implements \JsonSerializable
{
    protected $name;

    protected $slug;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        if ($this->name === null) {
            $this->name = trim($name);
        }
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        if ($this->slug === null) {
            $this->slug = trim($slug);
        }
    }

    abstract public function jsonSerialize();
}
