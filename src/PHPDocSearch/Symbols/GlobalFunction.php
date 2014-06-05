<?php

namespace PHPDocSearch\Symbols;

class GlobalFunction extends GlobalSymbol
{
    /**
     * Get the URL of this function on php.net
     *
     * @return string
     */
    private function makeLink()
    {
        return 'http://php.net/' . $this->slug;
    }

    /**
     * Get the JSON representation of this object
     *
     * @return \stdClass
     */
    public function jsonSerialize()
    {
        return (object) [
            'name' => $this->name,
            'book' => $this->book->getShortName(),
            'link' => $this->makeLink(),
        ];
    }
}
