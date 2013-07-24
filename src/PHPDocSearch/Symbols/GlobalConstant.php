<?php

namespace PHPDocSearch\Symbols;

class GlobalConstant extends GlobalSymbol
{
    private $type;

    private function makeLink()
    {
        return 'http://php.net/' . $this->slug;
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
            'book' => $this->book->getShortName(),
            'link' => $this->makeLink(),
        ];
    }
}
