<?php

namespace PHPDocSearch\Web\Search;

use \PHPDocSearch\PDOProvider;

class QueryResolver
{
    private $queryCache;

    private $db;

    private $dataMapper;

    public function __construct(QueryCache $queryCache, PDOProvider $dbProvider, DataMapper $dataMapper)
    {
        $this->queryCache = $queryCache;
        $this->db = $dbProvider->getConnection();
        $this->dataMapper = $dataMapper;
    }

    private function buildStatement($query)
    {
        $queryParts = $queryParams = [];

        if ($query->searchBooks()) {
            $queryParts[] = "
                SELECT 'book' AS `object_type`, NULL AS `parent`, `short_name` AS `name`, `full_name` as `full`, `slug`, NULL as `type`
                FROM `books`
                WHERE `short_name` = :bookName OR `slug` = :bookSlug
            ";
            $queryParams['bookName'] = $query->getElement(0);
            $queryParams['bookSlug'] = $query->getElement(0);
        }

        if ($query->searchClasses()) {
            $queryParts[] = "
                SELECT 'class' AS `object_type`, `b`.`short_name` AS `parent`, `c`.`name`, NULL as `full`, `c`.`slug`, NULL as `type`
                FROM `classes` `c`
                LEFT JOIN `books` `b` ON `c`.`book_id` = `b`.`id`
                WHERE `c`.`name` = :className
            ";
            $queryParams['className'] = $query->getElement(0);
        }

        if ($query->searchClassMethods()) {
            $queryParts[] = "
                SELECT 'class_method' AS `object_type`, `o`.`name` AS `parent`, `m`.`name`,
                       `c`.`name` as `full`, `m`.`slug`, `o`.`slug` as `type`
                FROM `classmethods` `m`
                INNER JOIN `classes` `c` ON `c`.`name` = :methodMemberClassName AND `m`.`class_id` = `c`.`id`
                LEFT JOIN `classes` `o` ON `m`.`owner_class_id` = `o`.`id`
                WHERE `m`.`name` = :classMethodName
            ";
            $queryParams['methodMemberClassName'] = $query->getElement(0);
            $queryParams['classMethodName'] = $query->getElement(1);
        }

        if ($query->searchClassProperties()) {
            $queryParts[] = "
                SELECT 'class_property' AS `object_type`, `o`.`name` AS `parent`, `m`.`name`,
                       `c`.`name` as `full`, `m`.`slug`, `o`.`slug` as `type`
                FROM `classprops` `m`
                INNER JOIN `classes` `c` ON `c`.`name` = :propertyMemberClassName AND `m`.`class_id` = `c`.`id`
                LEFT JOIN `classes` `o` ON `m`.`owner_class_id` = `o`.`id`
                WHERE `m`.`name` = :classPropertyName
            ";
            $queryParams['propertyMemberClassName'] = $query->getElement(0);
            $queryParams['classPropertyName'] = $query->getElement(1);
        }

        if ($query->searchClassConstants()) {
            $queryParts[] = "
                SELECT 'class_constant' AS `object_type`, `o`.`name` AS `parent`, `m`.`name`,
                       `c`.`name` as `full`, `m`.`slug`, `o`.`slug` as `type`
                FROM `classconstants` `m`
                INNER JOIN `classes` `c` ON `c`.`name` = :constantMemberClassName AND `m`.`class_id` = `c`.`id`
                LEFT JOIN `classes` `o` ON `m`.`owner_class_id` = `o`.`id`
                WHERE `m`.`name` = :classConstantName
            ";
            $queryParams['constantMemberClassName'] = $query->getElement(0);
            $queryParams['classConstantName'] = $query->getElement(1);
        }

        if ($query->searchConfigOptions()) {
            $queryParts[] = "
                SELECT 'config_option' AS `object_type`, `b`.`short_name` AS `parent`, `i`.`name`, `b`.`slug` as `full`, `i`.`slug`, `i`.`type`
                FROM `inisettings` `i`
                LEFT JOIN `books` `b` ON `i`.`book_id` = `b`.`id`
                WHERE `i`.`name` = :configOptionName
            ";
            $queryParams['configOptionName'] = implode('.', $query->getElements());
        }

        if ($query->searchConstants()) {
            $queryParts[] = "
                SELECT 'constant' AS `object_type`, `b`.`short_name` AS `parent`, `c`.`name`, NULL as `full`, `c`.`slug`, `c`.`type`
                FROM `constants` `c`
                LEFT JOIN `books` `b` ON `c`.`book_id` = `b`.`id`
                WHERE `c`.`name` = :constantName
            ";
            $queryParams['constantName'] = $query->getElement(0);
        }

        if ($query->searchControlStructures()) {
            $queryParts[] = "
                SELECT 'control_structure' AS `object_type`, NULL AS `parent`, `name`, NULL as `full`, `slug`, NULL as `type`
                FROM `controlstructures` `c`
                WHERE `c`.`name` = :controlStructureName
            ";
            $queryParams['controlStructureName'] = $query->getElement(0);
        }

        if ($query->searchFunctions()) {
            $queryParts[] = "
                SELECT 'function' AS `object_type`, `b`.`short_name` AS `parent`, `f`.`name`, NULL as `full`, `f`.`slug`, NULL as `type`
                FROM `functions` `f`
                LEFT JOIN `books` `b` ON `f`.`book_id` = `b`.`id`
                WHERE `f`.`name` = :functionName
            ";
            $queryParams['functionName'] = $query->getElement(0);
        }

        if ($query->searchMagicMethods()) {
            $queryParts[] = "
                SELECT 'magic_mathod' AS `object_type`, NULL AS `parent`, `m`.`name`, NULL as `full`, `m`.`slug`, NULL as `type`
                FROM `magicmethods` `m`
                WHERE `m`.`name` = :magicMethodName
            ";
            $queryParams['magicMethodName'] = '__' . ltrim($query->getElement(0), '_');
        }

        $queryString = count($queryParts) > 1 ? '(' . implode(') UNION (', $queryParts) . ')' : $queryParts[0];
        $stmt = $this->db->prepare($queryString);
        foreach ($queryParams as $name => $value) {
            $stmt->bindValue($name, $value, \PDO::PARAM_STR);
        }

        return $stmt;
    }

    private function queryDatabase($query)
    {
        $stmt = $this->buildStatement($query);
        $stmt->execute();

        $result = [];
        foreach ($stmt as $row) {
            $result[] = $this->dataMapper->map($row);
        }

        return $result;
    }

    public function resolve(Query $query)
    {
        $result = $this->queryCache->retrieve($query);

        if (!$result) {
            $result = $this->queryDatabase($query);
            $this->queryCache->store($query, $result);
        }

        return $result;
    }
}
