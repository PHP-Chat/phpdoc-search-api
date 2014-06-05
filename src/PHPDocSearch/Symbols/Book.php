<?php

namespace PHPDocSearch\Symbols;

class Book extends Symbol
{
    /**
     * Primary key in database
     *
     * @var int
     */
    private $id;

    /**
     * Short name of this book
     *
     * @var
     */
    private $shortName;

    /**
     * Config options defined in this book
     *
     * @var ConfigOption[]
     */
    private $configOptions = [];

    /**
     * Functions defined in this book
     *
     * @var GlobalFunction[]
     */
    private $functions = [];

    /**
     * Constants defined in this book
     *
     * @var GlobalConstant[]
     */
    private $constants = [];

    /**
     * Classes defined in this book
     *
     * @var GlobalClass[]
     */
    private $classes = [];

    /**
     * Get the URL of this book on php.net
     *
     * @return string
     */
    private function makeLink()
    {
        return 'http://php.net/book.' . $this->slug;
    }

    /**
     * Set the primary key of this book in the database
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = (int) $id;
    }

    /**
     * Get the primary key of this book in the database
     *
     * return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the short name of this book
     *
     * @param string $shortName
     */
    public function setShortName($shortName)
    {
        $this->shortName = (string) $shortName;
    }

    /**
     * Get the short name of this book
     *
     * @return int
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * Add a symbol defined by this book
     *
     * @param GlobalSymbol $symbol
     */
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

    /**
     * Get config options defined by this book
     *
     * @return ConfigOption[]
     */
    public function getConfigOptions()
    {
        return $this->configOptions;
    }

    /**
     * Get functions defined by this book
     *
     * @return GlobalFunction[]
     */
    public function getFunctions()
    {
        return $this->functions;
    }

    /**
     * Get constants defined by this book
     *
     * @return GlobalConstant[]
     */
    public function getConstants()
    {
        return $this->constants;
    }

    /**
     * Get classes defined by this book
     *
     * @return GlobalClass[]
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * Get the JSON representation of this object
     *
     * @return \stdClass
     */
    public function jsonSerialize()
    {
        return (object) [
            'name' => $this->shortName,
            'full' => $this->name,
            'link' => $this->makeLink(),
        ];
    }
}
