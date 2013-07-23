<?php

function fatal_error($msg)
{
    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
    trigger_error($msg, E_USER_ERROR);
}

if (!isset($_GET['q'])) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
    exit;
}

$phpDocBaseDir = realpath(__DIR__ . '/../../');

$configFile = $phpDocBaseDir . '/config.php';
if (!is_file($configFile)) {
    fatal_error('Configuration file missing! Please use the setup script for installation');
}
require $configFile;

try {
    $db = new PDO("mysql:host={$config['dbhost']};dbname={$config['dbname']};charset=utf8", $config['dbuser'], $config['dbpass']);
    $db->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,   false);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $functionSearchStmt = $db->prepare("
        SELECT f.name, b.short_name AS book, CONCAT('http://php.net/', f.slug) AS link
        FROM functions f
        LEFT JOIN books b ON f.book_id = b.id
        WHERE f.name = :searchName
    ");
    $functionSearchStmt->bindParam('searchName', $searchName, PDO::PARAM_STR);

    $classMethodSearchStmt = $db->prepare("
        SELECT m.name as method, c.name AS class, o.name AS owner_class, b.short_name AS book, CONCAT('http://php.net/', m.slug) AS link
        FROM classmethods m
        LEFT JOIN classes c ON m.class_id = c.id
        LEFT JOIN classes o ON m.owner_class_id = o.id
        LEFT JOIN books b ON c.book_id = b.id
        WHERE c.name = :className AND m.name = :methodName
    ");
    $classMethodSearchStmt->bindParam('className',  $className,  PDO::PARAM_STR);
    $classMethodSearchStmt->bindParam('methodName', $methodName, PDO::PARAM_STR);

    $classPropertySearchStmt = $db->prepare("
        SELECT p.name as property, c.name AS class, o.name AS owner_class, b.short_name AS book, CONCAT('http://php.net/', o.slug, '#', p.slug) AS link
        FROM classprops p
        LEFT JOIN classes c ON p.class_id = c.id
        LEFT JOIN classes o ON p.owner_class_id = o.id
        LEFT JOIN books b ON c.book_id = b.id
        WHERE c.name = :className AND p.name = :propertyName
    ");
    $classPropertySearchStmt->bindParam('className',    $className,    PDO::PARAM_STR);
    $classPropertySearchStmt->bindParam('propertyName', $propertyName, PDO::PARAM_STR);

    $configOptionSearchStmt = $db->prepare("
        SELECT i.name, b.short_name AS book, CONCAT('http://php.net/', If(b.id IS NULL, 'ini.core', CONCAT(b.slug, '.configuration')), '#', i.slug) AS link, i.type
        FROM inisettings i
        LEFT JOIN books b ON i.book_id = b.id
        WHERE i.name = :searchName
    ");
    $configOptionSearchStmt->bindParam('searchName', $searchName, PDO::PARAM_STR);

    $doubleIdentifierSearchStmt = $db->prepare("
        (
            SELECT 'class_method' AS entity_type, cm.name as entity_name, c.name AS class, o.name AS owner_class, b.short_name AS book, CONCAT('http://php.net/', cm.slug) AS link, NULL AS type
            FROM classmethods cm
            LEFT JOIN classes c ON cm.class_id = c.id
            LEFT JOIN classes o ON cm.owner_class_id = o.id
            LEFT JOIN books b ON c.book_id = b.id
            WHERE c.name = :cmSearchName1 AND cm.name = :cmSearchName2
        ) UNION (
            SELECT 'class_property' AS entity_type, cp.name as entity_name, c.name AS class, o.name AS owner_class, b.short_name AS book, CONCAT('http://php.net/', o.slug, '#', cp.slug) AS link, NULL AS type
            FROM classprops cp
            LEFT JOIN classes c ON cp.class_id = c.id
            LEFT JOIN classes o ON cp.owner_class_id = o.id
            LEFT JOIN books b ON c.book_id = b.id
            WHERE c.name = :cpSearchName1 AND cp.name = :cpSearchName2
        ) UNION (
            SELECT 'class_constant' AS entity_type, cc.name as entity_name, c.name AS class, o.name AS owner_class, b.short_name AS book, CONCAT('http://php.net/', o.slug, '#', cc.slug) AS link, NULL AS type
            FROM classconstants cc
            LEFT JOIN classes c ON cc.class_id = c.id
            LEFT JOIN classes o ON cc.owner_class_id = o.id
            LEFT JOIN books b ON c.book_id = b.id
            WHERE c.name = :ccSearchName1 AND cc.name = :ccSearchName2
        ) UNION (
            SELECT 'constant' AS entity_type, c.name as entity_name, NULL AS class, NULL AS owner_class, b.short_name AS book, CONCAT('http://php.net/', If(b.id IS NULL, 'errorfunc', b.slug), '.constants#', c.slug) AS link, c.type
            FROM constants c
            LEFT JOIN books b ON c.book_id = b.id
            WHERE c.name = :cSearchName
        ) UNION (
            SELECT 'ini' AS entity_type, i.name as entity_name, NULL AS class, NULL AS owner_class, b.short_name AS book, CONCAT('http://php.net/', If(b.id IS NULL, 'ini.core', CONCAT(b.slug, '.configuration')), '#', i.slug) AS link, i.type
            FROM inisettings i
            LEFT JOIN books b ON i.book_id = b.id
            WHERE i.name = :iSearchName
        )
    ");
    $doubleIdentifierSearchStmt->bindParam('cmSearchName1', $searchName1,         PDO::PARAM_STR);
    $doubleIdentifierSearchStmt->bindParam('cmSearchName2', $searchName2,         PDO::PARAM_STR);
    $doubleIdentifierSearchStmt->bindParam('cpSearchName1', $searchName1,         PDO::PARAM_STR);
    $doubleIdentifierSearchStmt->bindParam('cpSearchName2', $searchName2,         PDO::PARAM_STR);
    $doubleIdentifierSearchStmt->bindParam('ccSearchName1', $searchName1,         PDO::PARAM_STR);
    $doubleIdentifierSearchStmt->bindParam('ccSearchName2', $searchName2,         PDO::PARAM_STR);
    $doubleIdentifierSearchStmt->bindParam('cSearchName',   $searchNameDoubleDot, PDO::PARAM_STR);
    $doubleIdentifierSearchStmt->bindParam('iSearchName',   $searchNameDot,       PDO::PARAM_STR);

    $singleIdentifierSearchStmt = $db->prepare("
        (
            SELECT 'constant' AS entity_type, c.name, b.short_name AS book, CONCAT('http://php.net/', If(b.id IS NULL, 'errorfunc', b.slug), '.constants#', c.slug) AS link, c.type
            FROM constants c
            LEFT JOIN books b ON c.book_id = b.id
            WHERE c.name = :oSearchName
        ) UNION (
            SELECT 'function' AS entity_type, f.name, b.short_name AS book, CONCAT('http://php.net/', f.slug) AS link, NULL AS type
            FROM functions f
            LEFT JOIN books b ON f.book_id = b.id
            WHERE f.name = :fSearchName
        ) UNION (
            SELECT 'class' AS entity_type, c.name, b.short_name AS book, CONCAT('http://php.net/', c.slug) AS link, NULL AS type
            FROM classes c
            LEFT JOIN books b ON c.book_id = b.id
            WHERE c.name = :cSearchName
        ) UNION (
            SELECT 'ini' AS entity_type, i.name, b.short_name AS book, CONCAT('http://php.net/', If(b.id IS NULL, 'ini.core', CONCAT(b.slug, '.configuration')), '#', i.slug) AS link, i.type
            FROM inisettings i
            LEFT JOIN books b ON i.book_id = b.id
            WHERE i.name = :iSearchName
        ) UNION (
            SELECT 'book' AS entity_type, b.short_name AS name, b.full_name AS book, CONCAT('http://php.net/book.', b.slug) AS link, NULL AS type
            FROM books b
            WHERE b.short_name = :bSearchName
        )
    ");
    $singleIdentifierSearchStmt->bindParam('oSearchName', $searchName, PDO::PARAM_STR);
    $singleIdentifierSearchStmt->bindParam('fSearchName', $searchName, PDO::PARAM_STR);
    $singleIdentifierSearchStmt->bindParam('cSearchName', $searchName, PDO::PARAM_STR);
    $singleIdentifierSearchStmt->bindParam('iSearchName', $searchName, PDO::PARAM_STR);
    $singleIdentifierSearchStmt->bindParam('bSearchName', $searchName, PDO::PARAM_STR);
} catch (PDOException $e) {
    fatal_error('Caught PDOException: ' . $e->getMessage());
}

$parts = preg_split('/\s*+(?:\.|::|->|_>)\s*/', str_replace('-', '_', trim($_GET['q'])), -1, PREG_SPLIT_NO_EMPTY);
$numParts = count($parts);

$result = (object) [];

if ($numParts === 0) {
    $result->count = 0;
} else if ($numParts === 1) {
    $searchName = $parts[0];

    if (substr($searchName, -2) === '()') {
        $searchName = trim(substr($searchName, 0, -2));

        $functionSearchStmt->execute();
        $result->count = $functionSearchStmt->rowCount();

        if ($result->count) {
            $result->functions = $functionSearchStmt->fetchAll();
        }
    } else {
        $singleIdentifierSearchStmt->execute();
        $result->count = $singleIdentifierSearchStmt->rowCount();

        foreach ($singleIdentifierSearchStmt as $row) {
            switch ($row['entity_type']) {
                case 'constant':
                    if (!isset($result->constants)) {
                        $result->constants = [];
                    }

                    $result->constants[] = (object) [
                        'name' => $row['name'],
                        'type' => $row['type'],
                        'book' => $row['book'],
                        'link' => $row['link'],
                    ];
                    break;

                case 'function':
                    if (!isset($result->functions)) {
                        $result->functions = [];
                    }

                    $result->functions[] = (object) [
                        'name' => $row['name'],
                        'book' => $row['book'],
                        'link' => $row['link'],
                    ];
                    break;

                case 'class':
                    if (!isset($result->classes)) {
                        $result->classes = [];
                    }

                    $result->classes[] = (object) [
                        'name' => $row['name'],
                        'book' => $row['book'],
                        'link' => $row['link'],
                    ];
                    break;

                case 'ini':
                    if (!isset($result->config)) {
                        $result->config = [];
                    }

                    $result->config[] = (object) [
                        'name' => $row['name'],
                        'type' => $row['type'],
                        'book' => $row['book'],
                        'link' => $row['link'],
                    ];
                    break;

                case 'book':
                    if (!isset($result->books)) {
                        $result->books = [];
                    }

                    $result->books[] = (object) [
                        'name' => $row['name'],
                        'full' => $row['book'],
                        'link' => $row['link'],
                    ];
                    break;
            }
        }
    }
} else if ($numParts === 2) {
    if (substr($parts[1], -2) === '()') {
        $methodName = trim(substr($parts[1], 0, -2));
        $className = $parts[0];

        $classMethodSearchStmt->execute();
        $result->count = $classMethodSearchStmt->rowCount();

        if ($result->count) {
            $result->class_methods = $classMethodSearchStmt->fetchAll();
        }
    } else if ($parts[1][0] === '$') {
        $propertyName = trim(substr($parts[1], 1));
        $className = $parts[0];

        $classPropertySearchStmt->execute();
        $result->count = $classPropertySearchStmt->rowCount();

        if ($result->count) {
            $result->class_properties = $classPropertySearchStmt->fetchAll();
        }
    } else {
        $searchName1 = $parts[0];
        $searchName2 = $parts[1];
        $searchNameDot = $searchName1 . '.' . $searchName2;
        $searchNameDoubleDot = $searchName1 . '::' . $searchName2;

        $doubleIdentifierSearchStmt->execute();
        $result->count = $doubleIdentifierSearchStmt->rowCount();

        foreach ($doubleIdentifierSearchStmt as $row) {
            switch ($row['entity_type']) {
                case 'class_method':
                    if (!isset($result->class_methods)) {
                        $result->class_methods = [];
                    }

                    $result->class_methods[] = (object) [
                        'method' => $row['entity_name'],
                        'class' => $row['class'],
                        'owner_class' => $row['owner_class'],
                        'book' => $row['book'],
                        'link' => $row['link'],
                    ];
                    break;

                case 'class_property':
                    if (!isset($result->class_properties)) {
                        $result->class_properties = [];
                    }

                    $result->class_properties[] = (object) [
                        'property' => $row['entity_name'],
                        'class' => $row['class'],
                        'owner_class' => $row['owner_class'],
                        'book' => $row['book'],
                        'link' => $row['link'],
                    ];
                    break;

                case 'class_constant':
                    if (!isset($result->class_constants)) {
                        $result->class_constants = [];
                    }

                    $result->class_constants[] = (object) [
                        'constant' => $row['entity_name'],
                        'class' => $row['class'],
                        'owner_class' => $row['owner_class'],
                        'book' => $row['book'],
                        'link' => $row['link'],
                    ];
                    break;

                case 'constant':
                    if (!isset($result->constants)) {
                        $result->constants = [];
                    }

                    $result->constants[] = (object) [
                        'name' => $row['entity_name'],
                        'type' => $row['type'],
                        'book' => $row['book'],
                        'link' => $row['link'],
                    ];
                    break;

                case 'ini':
                    if (!isset($result->config)) {
                        $result->config = [];
                    }

                    $result->config[] = (object) [
                        'name' => $row['entity_name'],
                        'type' => $row['type'],
                        'book' => $row['book'],
                        'link' => $row['link'],
                    ];
                    break;
            }
        }
    }
} else {
    $searchName = implode('.', $parts);

    $configOptionSearchStmt->execute();
    $result->count = $configOptionSearchStmt->rowCount();

    if ($result->count) {
        $result->config = $configOptionSearchStmt->fetchAll();
    }
}

header('Content-Type: application/json');
exit(json_encode($result, JSON_PRETTY_PRINT));
