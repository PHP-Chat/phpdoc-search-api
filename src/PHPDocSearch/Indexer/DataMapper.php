<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Environment,
    \PHPDocSearch\PDOProvider,
    \PHPDocSearch\Logger,
    \PHPDocSearch\Symbols\Book,
    \PHPDocSearch\Symbols\ClassConstant,
    \PHPDocSearch\Symbols\ClassMethod,
    \PHPDocSearch\Symbols\ClassProperty,
    \PHPDocSearch\Symbols\ConfigOption,
    \PHPDocSearch\Symbols\ControlStructure,
    \PHPDocSearch\Symbols\MagicMethod,
    \PHPDocSearch\Symbols\GlobalClass,
    \PHPDocSearch\Symbols\GlobalConstant,
    \PHPDocSearch\Symbols\GlobalFunction;

class DataMapper
{
    /**
     * @var Environment
     */
    private $env;

    /**
     * @var \PDO
     */
    private $db;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $startTime;

    /**
     * @var \PDOStatement[]
     */
    private $statements = [];

    /**
     * Constructor
     *
     * @param Environment $env
     * @param PDOProvider $dataProvider
     * @param Logger $logger
     */
    public function __construct(Environment $env, PDOProvider $dataProvider, Logger $logger)
    {
        $this->env = $env;
        $this->db = $dataProvider->getConnection();
        $this->logger = $logger;

        $this->startTime = $env->getStartTime()->format('Y-m-d H:i:s');
    }

    /**
     * Get a cached PDOStatement by ID
     *
     * @param string $sql
     * @return \PDOStatement
     */
    private function getStatement($sql)
    {
        if (!isset($this->statements[$sql])) {
            $this->statements[$sql] = $this->db->prepare($sql);

            if (strpos($sql, ':last_seen') !== false) {
                $this->statements[$sql]->bindValue('last_seen', $this->startTime);
            }
        }

        return $this->statements[$sql];
    }

    /**
     * Insert a ClassMethod into the database
     *
     * @param ClassMethod $method
     * @param GlobalClass $memberClass
     */
    private function insertClassMethod(ClassMethod $method, GlobalClass $memberClass)
    {
        $this->logger->log('  Inserting method ' . $method->getName());

        $stmt = $this->getStatement('
            INSERT INTO `classmethods`
                (`class_id`, `owner_class_id`, `slug`, `name`)
            VALUES
                (:class_id, :iowner_class_id, :islug, :name)
            ON DUPLICATE KEY UPDATE
                `owner_class_id` = :uowner_class_id,
                `slug` = :uslug,
                `last_seen` = :last_seen
        ');

        $classId = $memberClass->getId();

        $ownerClass = $method->getOwnerClass();
        if ($ownerClass->getId() === null) {
            $this->insertClass($ownerClass);
        }
        $ownerClassId = $ownerClass->getId();

        $stmt->bindValue(':class_id',        $classId,           \PDO::PARAM_INT);
        $stmt->bindValue(':iowner_class_id', $ownerClassId,      \PDO::PARAM_INT);
        $stmt->bindValue(':uowner_class_id', $ownerClassId,      \PDO::PARAM_INT);
        $stmt->bindValue(':islug',           $method->getSlug(), \PDO::PARAM_STR);
        $stmt->bindValue(':uslug',           $method->getSlug(), \PDO::PARAM_STR);
        $stmt->bindValue(':name',            $method->getName(), \PDO::PARAM_STR);

        $stmt->execute();
    }

    /**
     * Insert a ClassProperty into the database
     *
     * @param ClassProperty $property
     * @param GlobalClass $memberClass
     */
    private function insertClassProperty(ClassProperty $property, GlobalClass $memberClass)
    {
        $this->logger->log('  Inserting property ' . $property->getName());

        $stmt = $this->getStatement("
            INSERT INTO `classprops`
                (`class_id`, `owner_class_id`, `slug`, `name`)
            VALUES
                (:class_id, :iowner_class_id, :islug, :name)
            ON DUPLICATE KEY UPDATE
                `owner_class_id` = :uowner_class_id,
                `slug` = :uslug,
                `last_seen` = :last_seen
        ");

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

    /**
     * Insert a ClassConstant into the database
     *
     * @param ClassConstant $constant
     * @param GlobalClass $memberClass
     */
    private function insertClassConstant(ClassConstant $constant, GlobalClass $memberClass)
    {
        $this->logger->log('  Inserting constant ' . $constant->getName());

        $stmt = $this->getStatement("
            INSERT INTO `classconstants`
                (`class_id`, `owner_class_id`, `slug`, `name`)
            VALUES
                (:class_id, :iowner_class_id, :islug, :name)
            ON DUPLICATE KEY UPDATE
                `owner_class_id` = :uowner_class_id,
                `slug` = :uslug,
                `last_seen` = :last_seen
        ");

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

    /**
     * Insert a Book into the database
     *
     * @param Book $book
     */
    public function insertBook(Book $book)
    {
        $this->logger->log('Inserting book ' . $book->getName() . '...');

        $stmt = $this->getStatement('
            INSERT INTO `books`
                (`slug`, `full_name`, `short_name`)
            VALUES
                (:slug, :ifull_name, :ishort_name)
            ON DUPLICATE KEY UPDATE
                `full_name`  = :ufull_name,
                `short_name` = :ushort_name,
                `last_seen`  = :last_seen
        ');

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

    /**
     * Insert a GlobalClass into the database
     *
     * @param GlobalClass $class
     */
    public function insertClass(GlobalClass $class)
    {
        if ($class->getId() !== null) {
            return;
        }

        $this->logger->log('Inserting class ' . $class->getName() . '...');

        $stmt = $this->getStatement('
            INSERT INTO `classes`
                (`book_id`, `slug`, `name`, `parent`)
            VALUES
                (:book_id, :slug, :name, :iparent)
            ON DUPLICATE KEY UPDATE
                `parent` = :uparent,
                `last_seen` = :last_seen
        ');

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

    /**
     * Insert a ConfigOption into the database
     *
     * @param ConfigOption $configOption
     */
    public function insertConfigOption(ConfigOption $configOption)
    {
        $this->logger->log('  Inserting config option ' . $configOption->getName());

        $stmt = $this->getStatement('
            INSERT INTO `inisettings`
                (`book_id`, `slug`, `name`, `type`)
            VALUES
                (:book_id, :slug, :iname, :itype)
            ON DUPLICATE KEY UPDATE
                `name` = :uname,
                `type` = :utype,
                `last_seen` = :last_seen
        ');

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

    /**
     * Insert a ControlStructure into the database
     *
     * @param ControlStructure $controlStructure
     */
    public function insertControlStructure(ControlStructure $controlStructure)
    {
        $this->logger->log('Inserting control structure ' . $controlStructure->getName());

        $stmt = $this->getStatement('
            INSERT INTO `controlstructures`
                (`slug`, `name`)
            VALUES
                (:islug, :name)
            ON DUPLICATE KEY UPDATE
                `slug` = :uslug,
                `last_seen` = :last_seen
        ');

        $stmt->bindValue(':islug', $controlStructure->getSlug(), \PDO::PARAM_STR);
        $stmt->bindValue(':name',  $controlStructure->getName(), \PDO::PARAM_STR);
        $stmt->bindValue(':uslug', $controlStructure->getSlug(), \PDO::PARAM_STR);

        $stmt->execute();
    }

    /**
     * Insert a MagicMethod into the database
     *
     * @param MagicMethod $magicMethod
     */
    public function insertMagicMethod(MagicMethod $magicMethod)
    {
        $this->logger->log('Inserting magic method ' . $magicMethod->getName());

        $stmt = $this->getStatement('
            INSERT INTO `magicmethods`
                (`slug`, `name`)
            VALUES
                (:islug, :name)
            ON DUPLICATE KEY UPDATE
                `slug` = :uslug,
                `last_seen` = :last_seen
        ');

        $stmt->bindValue(':islug', $magicMethod->getSlug(), \PDO::PARAM_STR);
        $stmt->bindValue(':name',  $magicMethod->getName(), \PDO::PARAM_STR);
        $stmt->bindValue(':uslug', $magicMethod->getSlug(), \PDO::PARAM_STR);

        $stmt->execute();
    }

    /**
     * Insert a GlobalConstant into the database
     *
     * @param GlobalConstant $constant
     */
    public function insertConstant(GlobalConstant $constant)
    {
        $this->logger->log('  Inserting constant ' . $constant->getName());

        $stmt = $this->getStatement('
            INSERT INTO `constants`
                (`book_id`, `slug`, `name`, `type`)
            VALUES
                (:book_id, :slug, :iname, :itype)
            ON DUPLICATE KEY UPDATE
                `name` = :uname,
                `type` = :utype,
                `last_seen` = :last_seen
        ');

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

    /**
     * Insert a GlobalFunction into the database
     *
     * @param GlobalFunction $function
     */
    public function insertFunction(GlobalFunction $function)
    {
        $this->logger->log('  Inserting function ' . $function->getName());

        $stmt = $this->getStatement('
            INSERT INTO `functions`
                (`book_id`, `slug`, `name`)
            VALUES
                (:book_id, :slug, :iname)
            ON DUPLICATE KEY UPDATE
                `name` = :uname,
                `last_seen` = :last_seen
        ');

        $stmt->bindValue(':book_id', $function->getBook()->getId(), \PDO::PARAM_INT);
        $stmt->bindValue(':slug',    $function->getSlug(),          \PDO::PARAM_STR);
        $stmt->bindValue(':iname',   $function->getName(),          \PDO::PARAM_STR);
        $stmt->bindValue(':uname',   $function->getName(),          \PDO::PARAM_STR);

        $stmt->execute();
    }
}
