<?php

function fatal_error($msg)
{
    fwrite(STDERR, "\nFATAL ERROR: " . $msg . "\n\n");
    exit(1);
}

$configFile = __DIR__ . '/../config.php';
if (!is_file($configFile)) {
    fatal_error('Configuration file missing! Please use the setup script for installation');
}

$config = [];
require $configFile;

$docRepos = ['base', 'en'];
$repoSyncCommand = 'git pull -q origin master';
$repoCleanupCommands = ['git checkout -q .', 'git clean -fq'];

$now = new DateTime;
$last_seen = $now->format('Y-m-d H:i:s');
$stale_age = $now->modify("-{$config['staleage']} days")->format('Y-m-d H:i:s');

// Resolve paths
echo "Resolving paths... ";
$phpDocBaseDir = realpath(__DIR__ . '/../');
$tempDir = $phpDocBaseDir . '/api/temp';

if (!is_dir($tempDir)) {
    if (file_exists($tempDir)) {
        echo "Failed\n";
        fatal_error('Configured temp path exists and is not a directory');
    } else if (!mkdir($tempDir, 0644, true)) {
        echo "Failed\n";
        fatal_error('Unable to create temp directory');
    }
}
$tempDir = realpath($tempDir);
$tempFile = $tempDir . '/.manual.xml';
echo "OK\n\n";

// Pull latest doc repositories
echo "Synchronising doc repositories\n";
$syncCommands = array_merge($repoCleanupCommands, [$repoSyncCommand]);
foreach ($docRepos as $repo) {
    echo "Synchronising $repo... ";
    chdir($phpDocBaseDir . '/' . $repo);

    foreach ($syncCommands as $cmd) {
        exec($cmd, $output, $exitCode);
        if ($exitCode) {
            echo "Failed\n";
            fatal_error("Command '$cmd' failed with error code $exitCode");
        }
    }

    echo "OK\n";
}
echo "\n";

// Build .manual.xml
echo "Building manual XML (this may take some time)... ";
chdir($phpDocBaseDir . '/base');
exec('php "' . $phpDocBaseDir . '/base/configure.php" "--output=' . $tempFile . '"', $output, $exitCode);
if ($exitCode) {
    echo "Failed\n";
    fatal_error("Command '$cmd' failed with error code $exitCode");
}
echo "OK\n\n";

// Clean up doc repositories
echo "Cleaning up doc repositories\n";
foreach ($docRepos as $repo) {
    echo "Cleaning up $repo... ";
    chdir($phpDocBaseDir . '/' . $repo);

    foreach ($syncCommands as $cmd) {
        exec($cmd, $output, $exitCode);
        if ($exitCode) {
            echo "Failed\n";
            fatal_error("Command '$cmd' failed with error code $exitCode");
        }
    }

    echo "OK\n";
}
echo "\n";

// Connect to database
echo "Setting up database connection... ";
try {
    $db = new PDO("mysql:host={$config['dbhost']};dbname={$config['dbname']};charset=utf8", $config['dbuser'], $config['dbpass']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create statements
    $bookInsertStmt = $db->prepare("
        INSERT INTO `books`
            (`slug`, `full_name`, `short_name`)
        VALUES
            (:slug, :ifull_name, :ishort_name)
        ON DUPLICATE KEY UPDATE
            `full_name` = :ufull_name,
            `short_name` = :ushort_name,
            `last_seen` = :last_seen
    ");
    $bookInsertStmt->bindParam(':slug', $slug, PDO::PARAM_STR);
    $bookInsertStmt->bindParam(':ifull_name', $full_name, PDO::PARAM_STR);
    $bookInsertStmt->bindParam(':ishort_name', $short_name, PDO::PARAM_STR);
    $bookInsertStmt->bindParam(':ufull_name', $full_name, PDO::PARAM_STR);
    $bookInsertStmt->bindParam(':ushort_name', $short_name, PDO::PARAM_STR);
    $bookInsertStmt->bindParam(':last_seen', $last_seen, PDO::PARAM_STR);

    $bookIdSelectStmt = $db->prepare("
        SELECT `id`
        FROM `books`
        WHERE `slug` = :slug
    ");
    $bookIdSelectStmt->bindParam(':slug', $slug);

    $bookIniInsertStmt = $db->prepare("
        INSERT INTO `inisettings`
            (`book_id`, `slug`, `name`, `type`)
        VALUES
            (:book_id, :slug, :iname, :itype)
        ON DUPLICATE KEY UPDATE
            `name` = :uname,
            `type` = :utype,
            `last_seen` = :last_seen
    ");
    $bookIniInsertStmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
    $bookIniInsertStmt->bindParam(':slug', $slug, PDO::PARAM_STR);
    $bookIniInsertStmt->bindParam(':iname', $name, PDO::PARAM_STR);
    $bookIniInsertStmt->bindParam(':itype', $type, PDO::PARAM_STR);
    $bookIniInsertStmt->bindParam(':uname', $name, PDO::PARAM_STR);
    $bookIniInsertStmt->bindParam(':utype', $type, PDO::PARAM_STR);
    $bookIniInsertStmt->bindParam(':last_seen', $last_seen, PDO::PARAM_STR);

    $bookConstInsertStmt = $db->prepare("
        INSERT INTO `constants`
            (`book_id`, `slug`, `name`, `type`)
        VALUES
            (:book_id, :slug, :iname, :itype)
        ON DUPLICATE KEY UPDATE
            `name` = :uname,
            `type` = :utype,
            `last_seen` = :last_seen
    ");
    $bookConstInsertStmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
    $bookConstInsertStmt->bindParam(':slug', $slug, PDO::PARAM_STR);
    $bookConstInsertStmt->bindParam(':iname', $name, PDO::PARAM_STR);
    $bookConstInsertStmt->bindParam(':itype', $type, PDO::PARAM_STR);
    $bookConstInsertStmt->bindParam(':uname', $name, PDO::PARAM_STR);
    $bookConstInsertStmt->bindParam(':utype', $type, PDO::PARAM_STR);
    $bookConstInsertStmt->bindParam(':last_seen', $last_seen, PDO::PARAM_STR);

    $bookFuncInsertStmt = $db->prepare("
        INSERT INTO `functions`
            (`book_id`, `slug`, `name`)
        VALUES
            (:book_id, :slug, :iname)
        ON DUPLICATE KEY UPDATE
            `name` = :uname,
            `last_seen` = :last_seen
    ");
    $bookFuncInsertStmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
    $bookFuncInsertStmt->bindParam(':slug', $slug, PDO::PARAM_STR);
    $bookFuncInsertStmt->bindParam(':iname', $name, PDO::PARAM_STR);
    $bookFuncInsertStmt->bindParam(':uname', $name, PDO::PARAM_STR);
    $bookFuncInsertStmt->bindParam(':last_seen', $last_seen, PDO::PARAM_STR);

    $bookClassInsertStmt = $db->prepare("
        INSERT INTO `classes`
            (`book_id`, `slug`, `name`, `parent`)
        VALUES
            (:book_id, :slug, :iname, :iparent)
        ON DUPLICATE KEY UPDATE
            `name` = :uname,
            `parent` = :uparent,
            `last_seen` = :last_seen
    ");
    $bookClassInsertStmt->bindParam(':book_id', $book_id, PDO::PARAM_INT);
    $bookClassInsertStmt->bindParam(':slug', $slug, PDO::PARAM_STR);
    $bookClassInsertStmt->bindParam(':iname', $name, PDO::PARAM_STR);
    $bookClassInsertStmt->bindParam(':uname', $name, PDO::PARAM_STR);
    $bookClassInsertStmt->bindParam(':iparent', $parent, PDO::PARAM_STR);
    $bookClassInsertStmt->bindParam(':uparent', $parent, PDO::PARAM_STR);
    $bookClassInsertStmt->bindParam(':last_seen', $last_seen, PDO::PARAM_STR);
} catch (PDOException $e) {
    echo "Failed\n";
    fatal_error("Caught PDOException: " . $e->getMessage());
}
echo "OK\n\n";

// Set up the DOM
echo "Loading manual XML... ";
$doc = new DOMDocument;
if (!$doc->load($tempFile)) {
    echo "Failed\n";
    fatal_error("Unable to load manual XML");
}

$xpath = new DOMXPath($doc);
$xpath->registerNamespace('db', 'http://docbook.org/ns/docbook');
$xpath->registerNamespace('pd', 'http://php.net/ns/phpdoc');
$xpath->registerNamespace('xml', 'http://www.w3.org/XML/1998/namespace');
echo "OK\n\n";

// Let's do some indexing!
echo "Indexing manual\n";
$books = $xpath->query('//db:book[starts-with(@xml:id, "book.")]');
foreach ($books as $book) {
    // Get meta about the book
    $bookSlug = $slug = explode('.', $book->getAttribute('xml:id'), 2)[1];

    $titleNodes = $xpath->query('./db:title', $book);
    if ($titleNodes->length) {
        $full_name = trim($titleNodes->item(0)->firstChild->data);
    } else {
        $full_name = '';
    }

    $titleAbbrevNodes = $xpath->query('./db:titleabbrev', $book);
    if ($titleAbbrevNodes->length) {
        $short_name = trim($titleAbbrevNodes->item(0)->firstChild->data);
    } else {
        $short_name = $full_name;
    }

    echo "Indexing book $slug ($full_name)... ";

    $bookInsertStmt->execute();
    $bookIdSelectStmt->execute();
    $book_id = (int) $bookIdSelectStmt->fetchColumn();
    $bookIdSelectStmt->closeCursor();

    // Config options
    $configOptions = $xpath->query(".//db:section[@xml:id='$bookSlug.configuration']//db:varlistentry[starts-with(@xml:id, 'ini.$bookSlug.')]", $book);
    foreach ($configOptions as $opt) {
        $slug = explode('.', $opt->getAttribute('xml:id'), 2)[1];
        $name = $type = '';

        $nameNodes = $xpath->query("./db:term/db:parameter", $opt);
        if ($nameNodes->length) {
            $name = $nameNodes->item(0)->firstChild->data;
        }

        $typeNodes = $xpath->query("./db:term/db:type", $opt);
        if ($typeNodes->length) {
            $type = $typeNodes->item(0)->firstChild->data;
        }

        $bookIniInsertStmt->execute();
    }

    // Constants
    $constants = $xpath->query(".//db:appendix[@xml:id='$bookSlug.constants']//db:varlistentry[starts-with(@xml:id, 'constant.')]", $book);
    foreach ($constants as $const) {
        $slug = explode('.', $const->getAttribute('xml:id'), 2)[1];
        $name = $type = '';

        $nameNodes = $xpath->query("./db:term/db:constant", $const);
        if ($nameNodes->length) {
            $name = $nameNodes->item(0)->firstChild->data;
        }

        $typeNodes = $xpath->query("./db:term/db:type", $const);
        if ($typeNodes->length) {
            $type = $typeNodes->item(0)->firstChild->data;
        }

        $bookConstInsertStmt->execute();
    }

    // Functions
    $functions = $xpath->query(".//db:reference[@xml:id='ref.$bookSlug']//db:refentry[starts-with(@xml:id, 'function.')]", $book);
    foreach ($functions as $func) {
        $slug = explode('.', $func->getAttribute('xml:id'), 2)[1];

        $name = $xpath->query("./db:refnamediv/db:refname", $func)->item(0)->firstChild->data;

        $bookFuncInsertStmt->execute();
    }

    // Classes
    $classes = $xpath->query(".//pd:classref[starts-with(@xml:id, 'class.')]", $book);
    foreach ($classes as $class) {
        $slug = explode('.', $class->getAttribute('xml:id'), 2)[1];

        $nameDef = $xpath->query(".//db:classsynopsis/db:ooclass/db:classname", $class);
        if (!$nameDef->length) {
            $nameDef = $xpath->query("./db:titleabbrev", $class);
        }
        $name = $nameDef->item(0)->firstChild->data;

        $parentDef = $xpath->query(".//db:classsynopsis/db:classsynopsisinfo/db:ooclass/db:modifier[text()='extends']/../db:classname", $class);
        if ($parentDef->length) {
            $parent = $parentDef->item(0)->firstChild->data;
        } else {
            $parent = '';
        }

        $bookClassInsertStmt->execute();
    }

    echo "OK\n";
}
echo "\n";

// Remove temp file
//unlink($tempFile);
