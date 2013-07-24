<?php

namespace PHPDocSearch\Symbols;

class Book extends Symbol
{
    private $id;

    private $shortName;

    private $configOptions = [];

    private $functions = [];

    private $constants = [];

    private $classes = [];

    private function makeLink()
    {
        return 'http://php.net/book.' . $this->slug;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setShortName($shortName)
    {
        $this->shortName = $shortName;
    }

    public function getShortName()
    {
        return $this->shortName;
    }

    public function addGlobalSymbol(GlobalSymbol $symbol)
    {
        $symbol->setBook($this);

        if ($symbol instanceof ConfigOption) {
            $this->configOptions[] = $symbol;
        } else if ($symbol instanceof GlobalFunction) {
            $this->functions[] = $symbol;
        } else if ($symbol instanceof GlobalConstant) {
            $this->constants[] = $symbol;
        } else if ($symbol instanceof GlobalClass) {
            $this->classes[] = $symbol;
        }
    }

    public function getConfigOptions()
    {
        return $this->configOptions;
    }

    public function getFunctions()
    {
        return $this->functions;
    }

    public function getConstants()
    {
        return $this->constants;
    }

    public function getClasses()
    {
        return $this->classes;
    }

    public function jsonSerialize()
    {
        return (object) [
            'name' => $this->shortName,
            'full' => $this->name,
            'link' => $this->makeLink(),
        ];
    }
}
