<?php

namespace PHPDocSearch\Symbols;

class ConfigOption extends GlobalSymbol
{
    private $type;

    private function makeLink()
    {
        $iniBase = $this->book ? $this->book->getSlug() . '.configuration' : 'ini.core';

        return 'http://php.net/' . $iniBase . '#' . $this->slug;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function jsonSerialize()
    {
        return (object) [
            'name' => $this->name,
            'type' => $this->type,
            'book' => $this->book ? $this->book->getShortName() : null,
            'link' => $this->makeLink(),
        ];
    }
}
