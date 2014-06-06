<?php

namespace PHPDocSearch\Web\Search;

use \PHPDocSearch\Symbols\Symbol,
    \PHPDocSearch\Symbols\BookFactory,
    \PHPDocSearch\Symbols\ClassFactory,
    \PHPDocSearch\Symbols\ClassMemberFactory,
    \PHPDocSearch\Symbols\ConfigOptionFactory,
    \PHPDocSearch\Symbols\ConstantFactory,
    \PHPDocSearch\Symbols\ControlStructureFactory,
    \PHPDocSearch\Symbols\FunctionFactory,
    \PHPDocSearch\Symbols\MagicMethodFactory;

class DataMapper
{
    /**
     * @var BookFactory
     */
    private $bookFactory;

    /**
     * @var ClassFactory
     */
    private $classFactory;

    /**
     * @var ClassMemberFactory
     */
    private $classMemberFactory;

    /**
     * @var ConfigOptionFactory
     */
    private $configOptionFactory;

    /**
     * @var ConstantFactory
     */
    private $constantFactory;

    /**
     * @var ControlStructureFactory
     */
    private $controlStructureFactory;

    /**
     * @var FunctionFactory
     */
    private $functionFactory;

    /**
     * @var MagicMethodFactory
     */
    private $magicMethodFactory;

    /**
     * Constructor
     *
     * @param BookFactory $bookFactory
     * @param ClassFactory $classFactory
     * @param ClassMemberFactory $classMemberFactory
     * @param ConfigOptionFactory $configOptionFactory
     * @param ConstantFactory $constantFactory
     * @param ControlStructureFactory $controlStructureFactory
     * @param FunctionFactory $functionFactory
     * @param MagicMethodFactory $magicMethodFactory
     */
    public function __construct(
        BookFactory $bookFactory,
        ClassFactory $classFactory,
        ClassMemberFactory $classMemberFactory,
        ConfigOptionFactory $configOptionFactory,
        ConstantFactory $constantFactory,
        ControlStructureFactory $controlStructureFactory,
        FunctionFactory $functionFactory,
        MagicMethodFactory $magicMethodFactory
    ) {
        $this->bookFactory = $bookFactory;
        $this->classFactory = $classFactory;
        $this->classMemberFactory = $classMemberFactory;
        $this->configOptionFactory = $configOptionFactory;
        $this->constantFactory = $constantFactory;
        $this->controlStructureFactory = $controlStructureFactory;
        $this->functionFactory = $functionFactory;
        $this->magicMethodFactory = $magicMethodFactory;
    }

    /**
     * Create a Symbol object from a row returned by the database
     *
     * @param array $row
     * @return Symbol|null
     */
    public function map(array $row)
    {
        switch ($row['object_type']) {
            case 'book':
                $book = $this->bookFactory->create();
                $book->setName($row['full']);
                $book->setShortName($row['name']);
                $book->setSlug($row['slug']);

                return $book;

            case 'class':
                $book = $this->bookFactory->create();
                $book->setShortName($row['parent']);

                $class = $this->classFactory->create();
                $class->setName($row['name']);
                $class->setBook($book);
                $class->setSlug($row['slug']);

                return $class;

            case 'class_method':
                $ownerClass = $this->classFactory->create();
                $ownerClass->setName($row['parent']);
                $ownerClass->setSlug($row['type']);

                $memberClass = $this->classFactory->create();
                $memberClass->setName($row['full']);

                $method = $this->classMemberFactory->createMethod();
                $method->setName($row['name']);
                $method->setOwnerClass($ownerClass);
                $method->setMemberClass($memberClass);
                $method->setSlug($row['slug']);

                return $method;

            case 'class_property':
                $ownerClass = $this->classFactory->create();
                $ownerClass->setName($row['parent']);
                $ownerClass->setSlug($row['type']);

                $memberClass = $this->classFactory->create();
                $memberClass->setName($row['full']);

                $property = $this->classMemberFactory->createProperty();
                $property->setName($row['name']);
                $property->setOwnerClass($ownerClass);
                $property->setMemberClass($memberClass);
                $property->setSlug($row['slug']);

                return $property;

            case 'class_constant':
                $ownerClass = $this->classFactory->create();
                $ownerClass->setName($row['parent']);
                $ownerClass->setSlug($row['type']);

                $memberClass = $this->classFactory->create();
                $memberClass->setName($row['full']);

                $constant = $this->classMemberFactory->createConstant();
                $constant->setName($row['name']);
                $constant->setOwnerClass($ownerClass);
                $constant->setMemberClass($memberClass);
                $constant->setSlug($row['slug']);

                return $constant;

            case 'config_option':
                $option = $this->configOptionFactory->create();
                $option->setName($row['name']);
                $option->setType($row['type']);
                $option->setSlug($row['slug']);

                if ($row['parent']) {
                    $book = $this->bookFactory->create();
                    $book->setShortName($row['parent']);
                    $book->setSlug($row['full']);
                    $option->setBook($book);
                }

                return $option;

            case 'constant':
                $book = $this->bookFactory->create();
                $book->setShortName($row['parent']);

                $constant = $this->configOptionFactory->create();
                $constant->setName($row['name']);
                $constant->setBook($book);
                $constant->setType($row['type']);
                $constant->setSlug($row['slug']);

                return $constant;

            case 'control_structure':
                $struct = $this->controlStructureFactory->create();
                $struct->setName($row['name']);
                $struct->setSlug($row['slug']);

                return $struct;

            case 'function':
                $book = $this->bookFactory->create();
                $book->setShortName($row['parent']);

                $function = $this->functionFactory->create();
                $function->setName($row['name']);
                $function->setBook($book);
                $function->setSlug($row['slug']);

                return $function;

            case 'magic_method':
                $method = $this->magicMethodFactory->create();
                $method->setName($row['name']);
                $method->setSlug($row['slug']);

                return $method;
        }

        return null;
    }
}
