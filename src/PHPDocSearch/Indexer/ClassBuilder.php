<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Symbols\ClassFactory,
    \PHPDocSearch\Symbols\ClassMemberFactory;

class ClassBuilder
{
    private $classFactory;

    private $classMemberFactory;

    public function __construct(ClassFactory $classFactory, ClassMemberFactory $classMemberFactory)
    {
        $this->classFactory       = $classFactory;
        $this->classMemberFactory = $classMemberFactory;
    }

    private function getMemberName($fqName)
    {
        $nameParts = preg_split('/(::|->)/', trim($fqName), -1, PREG_SPLIT_NO_EMPTY);
        return array_pop($nameParts);
    }

    private function processClassSynopsis($baseEl, $class, $xmlWrapper, $classRegistry)
    {
        $class->setSlug($baseEl->getAttribute('xml:id'));

        foreach ($xmlWrapper->query(".//db:classsynopsis/db:classsynopsisinfo/*", $baseEl) as $infoEl) {
            switch (strtolower($infoEl->tagName)) {
                case 'ooclass':
                    if (!$classNameEl = $xmlWrapper->getFirst('./db:classname', $infoEl)) {
                        continue 2;
                    }
                    $className = $classNameEl->textContent;

                    $modifier = $xmlWrapper->getFirst('./db:modifier', $infoEl);
                    if ($modifier && trim(strtolower($modifier->textContent)) === 'extends') {
                        $classRegistry->acquireParent($class, $className);
                    } else {
                        $class->setName($className);
                    }
                    break;

                case 'oointerface':
                    if ($interface = $xmlWrapper->getFirst('./db:interfacename', $infoEl)) {
                        $classRegistry->acquireInterface($class, $interface->textContent);
                    }
                    break;
            }
        }

        if ($class->getName() === null) {
            if ($className = $xmlWrapper->getFirst(".//db:classsynopsis/db:ooclass/db:classname", $baseEl)) {
                $class->setName($className->textContent);
            } else if ($titleAbbrev = $xmlWrapper->getFirst("./db:titleabbrev", $baseEl)) {
                $class->setName($titleAbbrev->textContent);
            }
        }
    }

    private function processMethods($baseEl, $class, $xmlWrapper)
    {
        foreach ($xmlWrapper->query(".//db:refentry", $baseEl) as $refEntry) {
            if ($refName = $xmlWrapper->getFirst(".//db:refnamediv/db:refname", $refEntry)) {
                $name = $this->getMemberName($refName->textContent);
                $slug = $refEntry->getAttribute('xml:id');

                $method = $this->classMemberFactory->createMethod();

                $method->setName($name);
                $method->setSlug($slug);

                $class->addMember($method);
            }
        }
    }

    private function processPropertiesAndConstants($baseEl, $class, $xmlWrapper)
    {
        foreach ($xmlWrapper->query(".//db:classsynopsis/db:fieldsynopsis", $baseEl) as $fieldRef) {
            $isConst = false;
            foreach ($xmlWrapper->query(".//db:modifier", $fieldRef) as $modifier) {
                if (trim(strtolower($modifier->textContent)) === 'const') {
                    $isConst = true;
                    break;
                }
            }

            if ($varName = $xmlWrapper->getFirst(".//db:varname[@linkend]", $fieldRef)) {
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

    public function build(\DOMElement $baseEl, ManualXMLWrapper $xmlWrapper, ClassRegistry $classRegistry)
    {
        $class = $this->classFactory->create();

        $this->processClassSynopsis($baseEl, $class, $xmlWrapper, $classRegistry);

        if (!$classRegistry->isRegistered($class->getName())) {
            $this->processMethods($baseEl, $class, $xmlWrapper);
            $this->processPropertiesAndConstants($baseEl, $class, $xmlWrapper);

            $classRegistry->register($class);

            return $class;
        }
    }
}
