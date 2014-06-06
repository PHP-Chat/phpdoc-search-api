<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\GlobalClass;

class ClassRegistry extends SymbolRegistry
{
    /**
     * Registry of classes waiting to acquire a parent class
     *
     * @var GlobalClass[]
     */
    private $pendingParents = [];

    /**
     * Registry of classes waiting to acquire an implemented interface
     *
     * @var GlobalClass[]
     */
    private $pendingInterfaces = [];

    /**
     * Add a GlobalClass to the registry
     *
     * @param GlobalClass $class
     */
    public function register(GlobalClass $class)
    {
        $name = $this->normalizeName($class->getName());

        if (!$this->isRegistered($name)) {
            $this->add($class);

            if (isset($this->pendingParents[$name])) {
                foreach ($this->pendingParents[$name] as $child) {
                    /**  @var GlobalClass $child */
                    $child->setParent($class);
                }

                unset($this->pendingParents[$name]);
            }

            if (isset($this->pendingInterfaces[$name])) {
                foreach ($this->pendingInterfaces[$name] as $implementor) {
                    /**  @var GlobalClass $implementor */
                    $implementor->addInterface($class);
                }

                unset($this->pendingInterfaces[$name]);
            }
        }
    }

    /**
     * Register a class as requiring a named parent class
     *
     * @param GlobalClass $class
     * @param string $parentName
     */
    public function acquireParent(GlobalClass $class, $parentName)
    {
        $name = $this->normalizeName($parentName);

        if ($this->isRegistered($name)) {
            $class->setParent($this->getSymbolByName($name));
        } else {
            if (!isset($this->pendingParents[$name])) {
                $this->pendingParents[$name] = [];
            }

            $this->pendingParents[$name][] = $class;
        }
    }

    /**
     * Register a class as requiring a named interface
     *
     * @param GlobalClass $class
     * @param string $interfaceName
     */
    public function acquireInterface(GlobalClass $class, $interfaceName)
    {
        $name = $this->normalizeName($interfaceName);

        if ($this->isRegistered($name)) {
            $class->addInterface($this->getSymbolByName($name));
        } else {
            if (!isset($this->pendingInterfaces[$name])) {
                $this->pendingInterfaces[$name] = [];
            }

            $this->pendingInterfaces[$name][] = $class;
        }
    }

    /**
     * Get the named GlobalClass
     *
     * @param string $name
     * @return GlobalClass
     */
    public function getSymbolByName($name)
    {
        return parent::getSymbolByName($name);
    }
}
