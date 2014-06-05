<?php

namespace PHPDocSearch\Symbols;

class ConfigOption extends GlobalSymbol
{
    /**
     * The data type of this config option
     *
     * @var string
     */
    private $type;

    /**
     * Get the URL of this config option on php.net
     *
     * @return string
     */
    private function makeLink()
    {
        $iniBase = $this->book ? $this->book->getSlug() . '.configuration' : 'ini.core';

        return 'http://php.net/' . $iniBase . '#' . $this->slug;
    }

    /**
     * Set the data type of this config option
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = (string) $type;
    }

    /**
     * Get the data type of this config option
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
            'book' => $this->book ? $this->book->getShortName() : null,
            'link' => $this->makeLink(),
        ];
    }
}
