<?php

namespace PHPDocSearch\Symbols;

class GlobalClass extends GlobalSymbol
{
    private $isNormalised = false;

    private $id;

    private $parent;

    private $interfaces = [];

    private $methods = [];

    private $properties = [];

    private $constants = [];

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

    private function makeLink()
    {
        return 'http://php.net/' . $this->slug;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setParent(GlobalClass $parent)
    {
        $this->parent = $parent;
    }

    public function hasParent()
    {
        return $this->parent !== null;
    }

    public function getParent()
    {
        return $this->parent;
    }

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

    public function getMethods()
    {
        $this->normaliseMembers();

        return $this->methods;
    }

    public function getProperties()
    {
        $this->normaliseMembers();

        return $this->properties;
    }

    public function getConstants()
    {
        $this->normaliseMembers();

        return $this->constants;
    }

    public function addInterface(GlobalClass $interface)
    {
        if (!in_array($interface, $this->interfaces, true)) {
            $this->interfaces[] = $interface;
        }
    }

    public function getInterfaces()
    {
        $this->normaliseMembers();

        return $this->interfaces;
    }

    public function jsonSerialize()
    {
        return (object) [
            'name' => $this->shortName,
            'book' => $this->book->getShortName(),
            'link' => $this->makeLink(),
        ];
    }
}
