<?php

namespace PHPDocSearch\Symbols;

class MagicMethod extends Symbol
{
    /**
     * Get the URL of this magic method on php.net
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
