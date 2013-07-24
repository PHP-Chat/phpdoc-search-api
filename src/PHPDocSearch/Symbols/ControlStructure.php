<?php

namespace PHPDocSearch\Symbols;

class ControlStructure extends Symbol
{
    private function makeLink()
    {
        return 'http://php.net/' . $this->slug;
    }

    public function jsonSerialize()
    {
        return (object) [
            'name' => $this->name,
            'link' => $this->makeLink(),
        ];
    }
}
