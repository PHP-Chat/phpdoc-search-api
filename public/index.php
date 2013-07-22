<?php

if (!isset($_GET['q'])) {
    header($_SERVER['PROTOCOL_VERSION'] . ' 400 Bad Request');
    exit;
}

$phpDocBaseDir = realpath(__DIR__ . '/../../');

$configFile = $phpDocBaseDir . '/config.php';
if (!is_file($configFile)) {
    trigger_error('Configuration file missing! Please use the setup script for installation', E_USER_ERROR);
}
require $configFile;

try {
    $db = new PDO("mysql:host={$config['dbhost']};dbname={$config['dbname']};charset=utf8", $config['dbuser'], $config['dbpass']);
    $db->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,   false);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    
    $singleTermSearchStmt = $db->prepare("
        SELECT *
        FROM ((
            SELECT 'constant' AS type, c.name, b.short_name AS book_name, CONCAT('http://php.net/', If(b.id IS NULL, 'errorfunc', b.slug), '.constants#', c.slug) AS ref_link
            FROM constants c
            LEFT JOIN books b ON c.book_id = b.id
        ) UNION (
            SELECT 'function' AS type, f.name, b.short_name AS book_name, CONCAT('http://php.net/', f.slug) AS ref_link
            FROM functions f
            LEFT JOIN books b ON f.book_id = b.id
        ) UNION (
            SELECT 'class' AS type, c.name, b.short_name AS book_name, CONCAT('http://php.net/', c.slug) AS ref_link
            FROM classes c
            LEFT JOIN books b ON c.book_id = b.id
        ) UNION (
            SELECT 'ini' AS type, i.name, b.short_name AS book_name, CONCAT('http://php.net/', If(b.id IS NULL, 'ini.core', Concat(b.slug, '.configuration')), '#', i.slug) AS ref_link
            FROM inisettings i
            LEFT JOIN books b ON i.book_id = b.id
        ) UNION (
            SELECT 'book' AS type, b.short_name AS name, b.short_name AS book_name, CONCAT('http://php.net/book.', b.slug) AS ref_link
            FROM books b
        )) m
        WHERE m.name = :searchName
    ");
    $singleTermSearchStmt->bindParam('searchName', $searchName, PDO::PARAM_STR);
} catch (PDOException $e) {
    trigger_error('Caught PDOException: ' . $e->getMessage(), E_USER_ERROR);
}

$searchName = $_GET['q'];
$singleTermSearchStmt->execute();

exit(json_encode($singleTermSearchStmt->fetchAll()));
