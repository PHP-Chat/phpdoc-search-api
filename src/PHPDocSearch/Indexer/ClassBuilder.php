<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\ClassFactory,
    \PHPDocSearch\Symbols\ClassMemberFactory;

class ClassBuilder
{
    private $classRegistry;

    private $classFactory;

    private $classMemberFactory;

    private $xpath;

    public function __construct(
        ClassRegistry $classRegistry,
        ClassFactory $classFactory,
        ClassMemberFactory $classMemberFactory,
        ManualXMLWrapper $xpath
    ) {
        $this->classRegistry      = $classRegistry;
        $this->classFactory       = $classFactory;
        $this->classMemberFactory = $classMemberFactory;
        $this->xpath              = $xpath;
    }

    private function getMemberName($fqName)
    {
        $nameParts = preg_split('/(::|->)/', trim($fqName), -1, PREG_SPLIT_NO_EMPTY);
        return array_pop($nameParts);
    }

    private function processClassSynopsis($baseEl, $class)
    {
        $class->setSlug($baseEl->getAttribute('xml:id'));

        foreach ($this->xpath->query(".//db:classsynopsis/db:classsynopsisinfo/*", $baseEl) as $infoEl) {
            switch (strtolower($infoEl->tagName)) {
                case 'ooclass':
                    if (!$classNameEl = $this->xpath->getFirst('./db:classname', $infoEl)) {
                        continue 2;
                    }
                    $className = $classNameEl->textContent;

                    $modifier = $this->xpath->getFirst('./db:modifier', $infoEl);
                    if ($modifier && trim(strtolower($modifier->textContent)) === 'extends') {
                        $this->classRegistry->acquireParent($class, $className);
                    } else {
                        $class->setName($className);
                    }
                    break;

                case 'oointerface':
                    if ($interface = $this->xpath->getFirst('./db:interfacename', $infoEl)) {
                        $this->classRegistry->acquireInterface($class, $interface->textContent);
                    }
                    break;
            }
        }

        if ($class->getName() === null) {
            if ($className = $this->xpath->getFirst(".//db:classsynopsis/db:ooclass/db:classname", $baseEl)) {
                $class->setName($className->textContent);
            } else if ($titleAbbrev = $this->xpath->getFirst("./db:titleabbrev", $baseEl)) {
                $class->setName($titleAbbrev->textContent);
            }
        }
    }

    private function processMethods($baseEl, $class)
    {
        foreach ($this->xpath->query(".//db:refentry", $baseEl) as $refEntry) {
            if ($refName = $this->xpath->getFirst(".//db:refnamediv/db:refname", $refEntry)) {
                $name = $this->getMemberName($refName->textContent);
                $slug = $refEntry->getAttribute('xml:id');

                $method = $this->classMemberFactory->createMethod();

                $method->setName($name);
                $method->setSlug($slug);

                $class->addMember($method);
            }
        }
    }

    private function processPropertiesAndConstants($baseEl, $class)
    {
        foreach ($this->xpath->query(".//db:classsynopsis/db:fieldsynopsis", $baseEl) as $fieldRef) {
            $isConst = false;
            foreach ($this->xpath->query(".//db:modifier", $fieldRef) as $modifier) {
                if (trim(strtolower($modifier->textContent)) === 'const') {
                    $isConst = true;
                    break;
                }
            }

            if ($varName = $this->xpath->getFirst(".//db:varname[@linkend]", $fieldRef)) {
                $name = $this->getMemberName($varName->textContent);
                $slug = $varName->getAttribute('linkend');

                $member = $isConst
                    ? $this->classMemberFactory->createConstant()
                    : $this->classMemberFactory->createProperty();

                $member->setName($name);
                $member->setSlug($slug);

                $class->addMember($member);
            }
        }
    }

    public function build(\DOMElement $baseEl)
    {
        $class = $this->classFactory->create();

        $this->processClassSynopsis($baseEl, $class);

        if (!$this->classRegistry->isRegistered($class->getName())) {
            $this->processMethods($baseEl, $class);
            $this->processPropertiesAndConstants($baseEl, $class);

            $this->classRegistry->register($class);

            return $class;
        }
    }
}
