<?php

namespace PHPDocSearch\Symbols;

class MagicMethod extends Symbol
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
