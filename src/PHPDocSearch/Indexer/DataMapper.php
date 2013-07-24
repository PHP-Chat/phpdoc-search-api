<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Environment,
    \PHPDocSearch\Logger,
    \PHPDocSearch\Symbols\Book,
    \PHPDocSearch\Symbols\ClassConstant,
    \PHPDocSearch\Symbols\ClassMethod,
    \PHPDocSearch\Symbols\ClassProperty,
    \PHPDocSearch\Symbols\ConfigOption,
    \PHPDocSearch\Symbols\ControlStructure,
    \PHPDocSearch\Symbols\GlobalClass,
    \PHPDocSearch\Symbols\GlobalConstant,
    \PHPDocSearch\Symbols\GlobalFunction;

class DataMapper
{
    private $env;

    private $db;

    private $logger;

    private $startTime;

    private $statements = [];

    public function __construct(Environment $env, callable $dataProvider, Logger $logger = null)
    {
        $this->env = $env;
        $this->db = $dataProvider();
        $this->logger = $logger;

        $this->startTime = $env->getStartTime()->format('Y-m-d H:i:s');
    }

    public function insertBook(Book $book)
    {
        $this->logger->log('Inserting book ' . $book->getName() . '...');

        if (!isset($this->statements['insertBook'])) {
            $this->statements['insertBook'] = $this->db->prepare("
                INSERT INTO `books`
                    (`slug`, `full_name`, `short_name`)
                VALUES
                    (:slug, :ifull_name, :ishort_name)
                ON DUPLICATE KEY UPDATE
                    `full_name`  = :ufull_name,
                    `short_name` = :ushort_name,
                    `last_seen`  = :last_seen
            ");

            $this->statements['insertBook']->bindValue('last_seen', $this->startTime);
        }

        $stmt = $this->statements['insertBook'];

        $stmt->bindValue('slug',        $book->getSlug(),      \PDO::PARAM_STR);
        $stmt->bindValue('ifull_name',  $book->getName(),      \PDO::PARAM_STR);
        $stmt->bindValue('ufull_name',  $book->getName(),      \PDO::PARAM_STR);
        $stmt->bindValue('ishort_name', $book->getShortName(), \PDO::PARAM_STR);
        $stmt->bindValue('ushort_name', $book->getShortName(), \PDO::PARAM_STR);

        $stmt->execute();

        $book->setId($this->db->lastInsertId());

        foreach ($book->getConfigOptions() as $configOption) {
            $this->insertConfigOption($configOption);
        }

        foreach ($book->getFunctions() as $function) {
            $this->insertFunction($function);
        }

        foreach ($book->getConstants() as $constant) {
            $this->insertConstant($constant);
        }
    }

    public function insertControlStructure(ControlStructure $controlStructure)
    {
        $this->logger->log('Inserting control structure ' . $controlStructure->getName());

        if (!isset($this->statements['insertControlStructure'])) {
            $this->statements['insertControlStructure'] = $this->db->prepare("
                INSERT INTO `controlstructures`
                    (`slug`, `name`)
                VALUES
                    (:islug, :name)
                ON DUPLICATE KEY UPDATE
                    `slug` = :uslug,
                    `last_seen` = :last_seen
            ");

            $this->statements['insertControlStructure']->bindValue('last_seen', $this->startTime);
        }

        $stmt = $this->statements['insertControlStructure'];

        $stmt->bindValue(':book_id', $controlStructure->getBook()->getId(), \PDO::PARAM_INT);
        $stmt->bindValue(':slug',    $controlStructure->getSlug(),          \PDO::PARAM_STR);
        $stmt->bindValue(':iname',   $controlStructure->getName(),          \PDO::PARAM_STR);
        $stmt->bindValue(':uname',   $controlStructure->getName(),          \PDO::PARAM_STR);

        $stmt->execute();
    }

    public function insertClass(GlobalClass $class)
    {
        if ($class->getId() === null) {
            $this->logger->log('Inserting class ' . $class->getName() . '...');

            if (!isset($this->statements['insertClass'])) {
                $this->statements['insertClass'] = $this->db->prepare("
                    INSERT INTO `classes`
                        (`book_id`, `slug`, `name`, `parent`)
                    VALUES
                        (:book_id, :slug, :name, :iparent)
                    ON DUPLICATE KEY UPDATE
                        `parent` = :uparent,
                        `last_seen` = :last_seen
                ");

                $this->statements['insertClass']->bindValue('last_seen', $this->startTime);
            }

            $stmt = $this->statements['insertClass'];

            $parent = $class->getParent();
            if ($parent && $parent->getId() === null) {
                $this->insertClass($parent);
            }
            $parentId = $parent ? $parent->getId() : null;

            $book = $class->getBook();
            $bookId = $book ? $book->getId() : null;

            $stmt->bindValue(':book_id', $bookId,           \PDO::PARAM_INT);
            $stmt->bindValue(':slug',    $class->getSlug(), \PDO::PARAM_STR);
            $stmt->bindValue(':name',    $class->getName(), \PDO::PARAM_STR);
            $stmt->bindValue(':iparent', $parentId,         \PDO::PARAM_INT);
            $stmt->bindValue(':uparent', $parentId,         \PDO::PARAM_INT);

            $stmt->execute();

            $class->setId($this->db->lastInsertId());

            foreach ($class->getMethods() as $method) {
                $this->insertClassMethod($method, $class);
            }

            foreach ($class->getProperties() as $property) {
                $this->insertClassProperty($property, $class);
            }

            foreach ($class->getConstants() as $constant) {
                $this->insertClassConstant($constant, $class);
            }
        }
    }

    public function insertConfigOption(ConfigOption $configOption)
    {
        $this->logger->log('  Inserting config option ' . $configOption->getName());

        if (!isset($this->statements['insertConfigOption'])) {
            $this->statements['insertConfigOption'] = $this->db->prepare("
                INSERT INTO `inisettings`
                    (`book_id`, `slug`, `name`, `type`)
                VALUES
                    (:book_id, :slug, :iname, :itype)
                ON DUPLICATE KEY UPDATE
                    `name` = :uname,
                    `type` = :utype,
                    `last_seen` = :last_seen
            ");

            $this->statements['insertConfigOption']->bindValue('last_seen', $this->startTime);
        }

        $stmt = $this->statements['insertConfigOption'];

        $book = $configOption->getBook();
        $bookId = $book ? $book->getId() : null;

        $stmt->bindValue(':book_id', $bookId,                  \PDO::PARAM_INT);
        $stmt->bindValue(':slug',    $configOption->getSlug(), \PDO::PARAM_STR);
        $stmt->bindValue(':iname',   $configOption->getName(), \PDO::PARAM_STR);
        $stmt->bindValue(':itype',   $configOption->getType(), \PDO::PARAM_STR);
        $stmt->bindValue(':uname',   $configOption->getName(), \PDO::PARAM_STR);
        $stmt->bindValue(':utype',   $configOption->getType(), \PDO::PARAM_STR);

        $stmt->execute();
    }

    public function insertConstant(GlobalConstant $constant)
    {
        $this->logger->log('  Inserting constant ' . $constant->getName());

        if (!isset($this->statements['insertConstant'])) {
            $this->statements['insertConstant'] = $this->db->prepare("
                INSERT INTO `constants`
                    (`book_id`, `slug`, `name`, `type`)
                VALUES
                    (:book_id, :slug, :iname, :itype)
                ON DUPLICATE KEY UPDATE
                    `name` = :uname,
                    `type` = :utype,
                    `last_seen` = :last_seen
            ");

            $this->statements['insertConstant']->bindValue('last_seen', $this->startTime);
        }

        $stmt = $this->statements['insertConstant'];

        $book = $constant->getBook();
        $bookId = $book ? $book->getId() : null;

        $stmt->bindValue(':book_id', $bookId,              \PDO::PARAM_INT);
        $stmt->bindValue(':slug',    $constant->getSlug(), \PDO::PARAM_STR);
        $stmt->bindValue(':iname',   $constant->getName(), \PDO::PARAM_STR);
        $stmt->bindValue(':itype',   $constant->getType(), \PDO::PARAM_STR);
        $stmt->bindValue(':uname',   $constant->getName(), \PDO::PARAM_STR);
        $stmt->bindValue(':utype',   $constant->getType(), \PDO::PARAM_STR);

        $stmt->execute();
    }

    public function insertFunction(GlobalFunction $function)
    {
        $this->logger->log('  Inserting function ' . $function->getName());

        if (!isset($this->statements['insertFunction'])) {
            $this->statements['insertFunction'] = $this->db->prepare("
                INSERT INTO `functions`
                    (`book_id`, `slug`, `name`)
                VALUES
                    (:book_id, :slug, :iname)
                ON DUPLICATE KEY UPDATE
                    `name` = :uname,
                    `last_seen` = :last_seen
            ");

            $this->statements['insertFunction']->bindValue('last_seen', $this->startTime);
        }

        $stmt = $this->statements['insertFunction'];

        $stmt->bindValue(':book_id', $function->getBook()->getId(), \PDO::PARAM_INT);
        $stmt->bindValue(':slug',    $function->getSlug(),          \PDO::PARAM_STR);
        $stmt->bindValue(':iname',   $function->getName(),          \PDO::PARAM_STR);
        $stmt->bindValue(':uname',   $function->getName(),          \PDO::PARAM_STR);

        $stmt->execute();
    }

    private function insertClassMethod(ClassMethod $method, GlobalClass $memberClass)
    {
        $this->logger->log('  Inserting method ' . $method->getName());

        if (!isset($this->statements['insertClassMethod'])) {
            $this->statements['insertClassMethod'] = $this->db->prepare("
                INSERT INTO `classmethods`
                    (`class_id`, `owner_class_id`, `slug`, `name`)
                VALUES
                    (:class_id, :iowner_class_id, :islug, :name)
                ON DUPLICATE KEY UPDATE
                    `owner_class_id` = :uowner_class_id,
                    `slug` = :uslug,
                    `last_seen` = :last_seen
            ");

            $this->statements['insertClassMethod']->bindValue('last_seen', $this->startTime);
        }

        $stmt = $this->statements['insertClassMethod'];

        $classId = $memberClass->getId();

        $ownerClass = $method->getOwnerClass();
        if ($ownerClass->getId() === null) {
            $this->insertClass($ownerClass);
        }
        $ownerClassId = $ownerClass->getId();

        $stmt->bindValue(':class_id',        $memberClass->getId(), \PDO::PARAM_INT);
        $stmt->bindValue(':iowner_class_id', $ownerClassId,         \PDO::PARAM_INT);
        $stmt->bindValue(':uowner_class_id', $ownerClassId,         \PDO::PARAM_INT);
        $stmt->bindValue(':islug',           $method->getSlug(),    \PDO::PARAM_STR);
        $stmt->bindValue(':uslug',           $method->getSlug(),    \PDO::PARAM_STR);
        $stmt->bindValue(':name',            $method->getName(),    \PDO::PARAM_STR);

        $stmt->execute();
    }

    private function insertClassProperty(ClassProperty $property, GlobalClass $memberClass)
    {
        $this->logger->log('  Inserting property ' . $property->getName());

        if (!isset($this->statements['insertClassProperty'])) {
            $this->statements['insertClassProperty'] = $this->db->prepare("
                INSERT INTO `classprops`
                    (`class_id`, `owner_class_id`, `slug`, `name`)
                VALUES
                    (:class_id, :iowner_class_id, :islug, :name)
                ON DUPLICATE KEY UPDATE
                    `owner_class_id` = :uowner_class_id,
                    `slug` = :uslug,
                    `last_seen` = :last_seen
            ");

            $this->statements['insertClassProperty']->bindValue('last_seen', $this->startTime);
        }

        $stmt = $this->statements['insertClassProperty'];

        $ownerClass = $property->getOwnerClass();
        if ($ownerClass->getId() === null) {
            $this->insertClass($ownerClass);
        }
        $ownerClassId = $ownerClass->getId();

        $stmt->bindValue(':class_id',        $memberClass->getId(), \PDO::PARAM_INT);
        $stmt->bindValue(':iowner_class_id', $ownerClassId,         \PDO::PARAM_INT);
        $stmt->bindValue(':uowner_class_id', $ownerClassId,         \PDO::PARAM_INT);
        $stmt->bindValue(':islug',           $property->getSlug(),  \PDO::PARAM_STR);
        $stmt->bindValue(':uslug',           $property->getSlug(),  \PDO::PARAM_STR);
        $stmt->bindValue(':name',            $property->getName(),  \PDO::PARAM_STR);

        $stmt->execute();
    }

    private function insertClassConstant(ClassConstant $constant, GlobalClass $memberClass)
    {
        $this->logger->log('  Inserting constant ' . $constant->getName());

        if (!isset($this->statements['insertClassConstant'])) {
            $this->statements['insertClassConstant'] = $this->db->prepare("
                INSERT INTO `classprops`
                    (`class_id`, `owner_class_id`, `slug`, `name`)
                VALUES
                    (:class_id, :iowner_class_id, :islug, :name)
                ON DUPLICATE KEY UPDATE
                    `owner_class_id` = :uowner_class_id,
                    `slug` = :uslug,
                    `last_seen` = :last_seen
            ");

            $this->statements['insertClassConstant']->bindValue('last_seen', $this->startTime);
        }

        $stmt = $this->statements['insertClassConstant'];

        $ownerClass = $constant->getOwnerClass();
        if ($ownerClass->getId() === null) {
            $this->insertClass($ownerClass);
        }
        $ownerClassId = $ownerClass->getId();

        $stmt->bindValue(':class_id',        $memberClass->getId(), \PDO::PARAM_INT);
        $stmt->bindValue(':iowner_class_id', $ownerClassId,         \PDO::PARAM_INT);
        $stmt->bindValue(':uowner_class_id', $ownerClassId,         \PDO::PARAM_INT);
        $stmt->bindValue(':islug',           $constant->getSlug(),  \PDO::PARAM_STR);
        $stmt->bindValue(':uslug',           $constant->getSlug(),  \PDO::PARAM_STR);
        $stmt->bindValue(':name',            $constant->getName(),  \PDO::PARAM_STR);

        $stmt->execute();
    }
}
