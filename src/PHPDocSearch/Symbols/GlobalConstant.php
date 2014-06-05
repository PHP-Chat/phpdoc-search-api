<?php

namespace PHPDocSearch\Symbols;

class GlobalConstant extends GlobalSymbol
{
    /**
     * The data type of this constant
     *
     * @var string
     */
    private $type;

    /**
     * Get the URL of this constant on php.net
     *
     * @return string
     */
    private function makeLink()
    {
        return 'http://php.net/' . $this->slug;
    }

    /**
     * Set the data type of this constant
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = (string) $type;
    }

    /**
     * Get the data type of this constant
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
            'type' => $this->type,
            'book' => $this->book->getShortName(),
            'link' => $this->makeLink(),
        ];
    }
}
