<?php

namespace PHPDocSearch\Symbols;

class GlobalClass extends GlobalSymbol
{
    /**
     * Whether the class member list has been normalised
     *
     * @var bool
     */
    private $isNormalised = false;

    /**
     * The parent class of this class
     *
     * @var GlobalClass
     */
    private $parent;

    /**
     * Interfaces implemented by this class
     *
     * @var GlobalClass[]
     */
    private $interfaces = [];

    /**
     * Method members of this class
     *
     * @var ClassMethod[]
     */
    private $methods = [];

    /**
     * Property members of this class
     *
     * @var ClassProperty[]
     */
    private $properties = [];

    /**
     * Constant members of this class
     *
     * @var ClassConstant[]
     */
    private $constants = [];

    /**
     * Inherit members from parent classes
     *
     * @param GlobalClass $class
     */
    private function inheritMembers(GlobalClass $class)
    {
        foreach ($class->getMethods() as $method) {
            $this->addMember($method);
        }

        foreach ($class->getProperties() as $property) {
            $this->addMember($property);
        }

        foreach ($class->getConstants() as $constant) {
            $this->addMember($constant);
        }

        foreach ($class->getInterfaces() as $interface) {
            $this->addInterface($interface);
        }
    }

    /**
     * Normalise the members of this class from parents
     */
    private function normaliseMembers()
    {
        if (!$this->isNormalised) {
            if ($this->hasParent()) {
                $this->inheritMembers($this->parent);
            }

            foreach ($this->interfaces as $interface) {
                $this->inheritMembers($interface);
            }

            $this->isNormalised = true;
        }
    }

    /**
     * Get the URL of this class on php.net
     *
     * @return string
     */
    private function makeLink()
    {
        return 'http://php.net/' . $this->slug;
    }

    /**
     * Set the parent class of this class
     *
     * @param GlobalClass $parent
     */
    public function setParent(GlobalClass $parent)
    {
        $this->parent = $parent;
    }

    /**
     * Determine whether this class inherits another class
     *
     * @return bool
     */
    public function hasParent()
    {
        return $this->parent !== null;
    }

    /**
     * Get the parent class of this class
     *
     * @return GlobalClass
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add a member to this class
     *
     * @param ClassMember $member
     */
    public function addMember(ClassMember $member)
    {
        if ($member instanceof ClassMethod) {
            $dataStore = &$this->methods;
        } else if ($member instanceof ClassProperty) {
            $dataStore = &$this->properties;
        } else if ($member instanceof ClassConstant) {
            $dataStore = &$this->constants;
        }

        $memberName = strtolower($member->getName());

        if (!isset($dataStore[$memberName])) {
            $member->setOwnerClass($this);
            $dataStore[$memberName] = $member;
        }
    }

    /**
     * Get the method members of this class
     *
     * @return ClassMethod[]
     */
    public function getMethods()
    {
        $this->normaliseMembers();

        return $this->methods;
    }

    /**
     * Get the property members of this class
     *
     * @return ClassProperty[]
     */
    public function getProperties()
    {
        $this->normaliseMembers();

        return $this->properties;
    }

    /**
     * Get the constant members of this class
     *
     * @return ClassConstant[]
     */
    public function getConstants()
    {
        $this->normaliseMembers();

        return $this->constants;
    }

    /**
     * Add an interface implemented by this class
     *
     * @param GlobalClass $interface
     */
    public function addInterface(GlobalClass $interface)
    {
        if (!in_array($interface, $this->interfaces, true)) {
            $this->interfaces[] = $interface;
        }
    }

    /**
     * Get the interfaces implemented by this class
     *
     * @return GlobalClass[]
     */
    public function getInterfaces()
    {
        $this->normaliseMembers();

        return $this->interfaces;
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
            'book' => $this->book->getShortName(),
            'link' => $this->makeLink(),
        ];
    }
}
