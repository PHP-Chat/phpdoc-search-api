<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\GlobalClass;

class ClassRegistry extends SymbolRegistry
{
    private $pendingParents = [];

    private $pendingInterfaces = [];

    public function register(GlobalClass $class)
    {
        $name = $this->normalizeName($class->getName());

        if (!$this->isRegistered($name)) {
            $this->add($class);

            if (isset($this->pendingParents[$name])) {
                foreach ($this->pendingParents[$name] as $child) {
                    $child->setParent($class);
                }

                unset($this->pendingParents[$name]);
            }

            if (isset($this->pendingInterfaces[$name])) {
                foreach ($this->pendingInterfaces[$name] as $implementor) {
                    $implementor->addInterface($class);
                }

                unset($this->pendingInterfaces[$name]);
            }
        }
    }

    public function acquireParent(GlobalClass $class, $parentName)
    {
        $name = $this->normalizeName($parentName);

        if (isset($this->classes[$name])) {
            $class->setParent($this->classes[$name]);
        } else {
            if (!isset($this->pendingParents[$name])) {
                $this->pendingParents[$name] = [];
            }

            $this->pendingParents[$name][] = $class;
        }
    }

    public function acquireInterface(GlobalClass $class, $interfaceName)
    {
        $name = $this->normalizeName($interfaceName);

        if (isset($this->classes[$name])) {
            $class->addInterface($this->classes[$name]);
        } else {
            if (!isset($this->pendingInterfaces[$name])) {
                $this->pendingInterfaces[$name] = [];
            }

            $this->pendingInterfaces[$name][] = $class;
        }
    }
}
