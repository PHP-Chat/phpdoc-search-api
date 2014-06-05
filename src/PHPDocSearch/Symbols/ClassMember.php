<?php

namespace PHPDocSearch\Symbols;

abstract class ClassMember extends Symbol
{
    /**
     * Get the class this symbol is defined in
     *
     * @var GlobalClass
     */
    protected $ownerClass;

    /**
     * Get the class this symbol is a member of
     *
     * @var GlobalClass
     */
    protected $memberClass;

    /**
     * Set the class this symbol is defined in
     *
     * @param GlobalClass $class
     */
    public function setOwnerClass(GlobalClass $class)
    {
        if ($this->ownerClass === null) {
            $this->ownerClass = $class;
        }
    }

    /**
     * Get the class this symbol is defined in
     *
     * @return GlobalClass
     */
    public function getOwnerClass()
    {
        return $this->ownerClass;
    }

    /**
     * Set the class this symbol is a member of
     *
     * @param GlobalClass $class
     */
    public function setMemberClass(GlobalClass $class)
    {
        if ($this->memberClass === null) {
            $this->memberClass = $class;
        }
    }

    /**
     * Get the class this symbol is a member of
     *
     * @return GlobalClass
     */
    public function getMemberClass()
    {
        return $this->memberClass;
    }
}
