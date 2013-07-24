<?php

namespace PHPDocSearch\Symbols;

class GlobalFunction extends GlobalSymbol
{
    private function makeLink()
    {
        return 'http://php.net/' . $this->slug;
    }

    public function jsonSerialize()
    {
        return (object) [
            'name' => $this->name,
            'book' => $this->book->getShortName(),
            'link' => $this->makeLink(),
        ];
    }
}
