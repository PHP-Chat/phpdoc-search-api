<?php

namespace PHPDocSearch\Symbols;

abstract class Symbol implements \JsonSerializable
{
    /**
     * The name of this symbol
     *
     * @var string
     */
    protected $name;

    /**
     * The manual slug of this symbol
     *
     * @var string
     */
    protected $slug;

    /**
     * Primary key in database
     *
     * @var int
     */
    protected $id;

    /**
     * Get the name of this symbol
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name of this symbol
     *
     * @param string $name
     */
    public function setName($name)
    {
        if ($this->name === null) {
            $this->name = trim($name);
        }
    }

    /**
     * Get the manual slug of this symbol
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set the manual slug of this symbol
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        if ($this->slug === null) {
            $this->slug = trim($slug);
        }
    }

    /**
     * Set the primary key of this symbol in the database
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = (int) $id;
    }

    /**
     * Get the primary key of this symbol in the database
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the JSON representation of this object
     *
     * @return \stdClass
     */
    abstract public function jsonSerialize();
}
