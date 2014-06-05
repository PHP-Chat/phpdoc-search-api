<?php

namespace PHPDocSearch\Symbols;

class ControlStructure extends Symbol
{
    /**
     * Get the URL of this control structure on php.net
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
            'link' => $this->makeLink(),
        ];
    }
}
