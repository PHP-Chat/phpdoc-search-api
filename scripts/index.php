<?php

use \PHPDocSearch\Indexer\ManualXPath,
    \PHPDocSearch\Indexer\BookRegistry,
    \PHPDocSearch\Indexer\ClassRegistry,
    \PHPDocSearch\Indexer\BookBuilder,
    \PHPDocSearch\Indexer\ClassBuilder,
    \PHPDocSearch\Indexer\ConfigOptionBuilder,
    \PHPDocSearch\Indexer\ConstantBuilder,
    \PHPDocSearch\Indexer\FunctionBuilder,
    \PHPDocSearch\Symbols\BookFactory,
    \PHPDocSearch\Symbols\ClassFactory,
    \PHPDocSearch\Symbols\ClassMemberFactory,
    \PHPDocSearch\Symbols\ConfigOptionFactory,
    \PHPDocSearch\Symbols\ConstantFactory,
    \PHPDocSearch\Symbols\FunctionFactory;

function fatal_error($msg)
{
    fwrite(STDERR, "\nFATAL ERROR: " . $msg . "\n\n");
    exit(1);
}

function do_exec($cmd)
{
    exec($cmd, $output, $exitCode);
    if ($exitCode) {
        echo "Failed\n";
        fatal_error("Command '$cmd' failed with error code $exitCode");
    }
}

require __DIR__ . '/autoload.php';

$docRepos = ['base', 'en'];
$repoSyncCommand = 'git pull -q origin master';
$repoCleanupCommands = ['git checkout -q .', 'git clean -fq'];

// Resolve paths
echo "Resolving paths... ";
$phpDocBaseDir = realpath(__DIR__ . '/../../');

$configFile = $phpDocBaseDir . '/config.php';
if (!is_file($configFile)) {
    fatal_error('Configuration file missing! Please use the setup script for installation');
}

$config = [];
require $configFile;

$tempDir = $phpDocBaseDir . '/temp';
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

// Get a standard timestamp for queries
$now = new \DateTime('now');
$last_seen = $now->format('Y-m-d H:i:s');
$stale_age = $now->modify("-{$config['staleage']} days")->format('Y-m-d H:i:s');

$hasWork = false;
/*
// Pull latest doc repositories
echo "Synchronising doc repositories\n";
$syncCommands = array_merge($repoCleanupCommands, [$repoSyncCommand]);
foreach ($docRepos as $repo) {
    echo "Synchronising $repo... ";
    chdir($phpDocBaseDir . '/' . $repo);

    $oldHead = trim(file_get_contents('.git/refs/heads/master'));

    foreach ($syncCommands as $cmd) {
        do_exec($cmd);
    }

    $newHead = trim(file_get_contents('.git/refs/heads/master'));

    if ($oldHead !== $newHead) {
        $hasWork = true;
    }

    echo "OK\n";
}
echo "\n";

if (!$hasWork) {
//    exit("No changes since last index run, nothing to do\n");
}

// Build .manual.xml
echo "Building manual XML (this may take some time)... ";
chdir($phpDocBaseDir . '/base');
do_exec('php "' . $phpDocBaseDir . '/base/configure.php" "--output=' . $tempFile . '"');
echo "OK\n\n";

// Clean up doc repositories
echo "Cleaning up doc repositories\n";
foreach ($docRepos as $repo) {
    echo "Cleaning up $repo... ";
    chdir($phpDocBaseDir . '/' . $repo);

    foreach ($syncCommands as $cmd) {
        do_exec($cmd);
    }

    echo "OK\n";
}
echo "\n";


// Connect to database
echo "Setting up database connection... ";
try {
    $db = new \PDO("mysql:host={$config['dbhost']};dbname={$config['dbname']};charset=utf8", $config['dbuser'], $config['dbpass']);
    $db->setAttribute(PDO::ATTR_ERRMODE,            PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES,   false);
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
    $bookInsertStmt->bindParam(':ifull_name',  $full_name,  PDO::PARAM_STR);
    $bookInsertStmt->bindParam(':ishort_name', $short_name, PDO::PARAM_STR);
    $bookInsertStmt->bindParam(':ufull_name',  $full_name,  PDO::PARAM_STR);
    $bookInsertStmt->bindParam(':ushort_name', $short_name, PDO::PARAM_STR);
    $bookInsertStmt->bindParam(':last_seen',   $last_seen,  PDO::PARAM_STR);

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
    $bookIniInsertStmt->bindParam(':book_id',   $book_id,   PDO::PARAM_INT);
    $bookIniInsertStmt->bindParam(':slug',      $slug,      PDO::PARAM_STR);
    $bookIniInsertStmt->bindParam(':iname',     $name,      PDO::PARAM_STR);
    $bookIniInsertStmt->bindParam(':itype',     $type,      PDO::PARAM_STR);
    $bookIniInsertStmt->bindParam(':uname',     $name,      PDO::PARAM_STR);
    $bookIniInsertStmt->bindParam(':utype',     $type,      PDO::PARAM_STR);
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
    $bookConstInsertStmt->bindParam(':book_id',   $book_id,   PDO::PARAM_INT);
    $bookConstInsertStmt->bindParam(':slug',      $slug,      PDO::PARAM_STR);
    $bookConstInsertStmt->bindParam(':iname',     $name,      PDO::PARAM_STR);
    $bookConstInsertStmt->bindParam(':itype',     $type,      PDO::PARAM_STR);
    $bookConstInsertStmt->bindParam(':uname',     $name,      PDO::PARAM_STR);
    $bookConstInsertStmt->bindParam(':utype',     $type,      PDO::PARAM_STR);
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
    $bookFuncInsertStmt->bindParam(':book_id',   $book_id,   PDO::PARAM_INT);
    $bookFuncInsertStmt->bindParam(':slug',      $slug,      PDO::PARAM_STR);
    $bookFuncInsertStmt->bindParam(':iname',     $name,      PDO::PARAM_STR);
    $bookFuncInsertStmt->bindParam(':uname',     $name,      PDO::PARAM_STR);
    $bookFuncInsertStmt->bindParam(':last_seen', $last_seen, PDO::PARAM_STR);

    $controlStructInsertStmt = $db->prepare("
        INSERT INTO `controlstructures`
            (`slug`, `name`)
        VALUES
            (:islug, :name)
        ON DUPLICATE KEY UPDATE
            `slug` = :uslug,
            `last_seen` = :last_seen
    ");
    $controlStructInsertStmt->bindParam(':book_id',   $book_id,   PDO::PARAM_INT);
    $controlStructInsertStmt->bindParam(':islug',     $slug,      PDO::PARAM_STR);
    $controlStructInsertStmt->bindParam(':name',      $name,      PDO::PARAM_STR);
    $controlStructInsertStmt->bindParam(':uslug',     $slug,      PDO::PARAM_STR);
    $controlStructInsertStmt->bindParam(':last_seen', $last_seen, PDO::PARAM_STR);

    $bookClassInsertStmt = $db->prepare("
        INSERT INTO `classes`
            (`book_id`, `slug`, `name`)
        VALUES
            (:book_id, :slug, :name)
        ON DUPLICATE KEY UPDATE
            `last_seen` = :last_seen
    ");
    $bookClassInsertStmt->bindParam(':book_id',   $book_id,   PDO::PARAM_INT);
    $bookClassInsertStmt->bindParam(':slug',      $slug,      PDO::PARAM_STR);
    $bookClassInsertStmt->bindParam(':name',      $name,      PDO::PARAM_STR);
    $bookClassInsertStmt->bindParam(':last_seen', $last_seen, PDO::PARAM_STR);

    $bookClassParentUpdateClassStmt = $db->prepare("
        UPDATE `classes`
        SET `parent` = :parent
        WHERE `id` = :id
    ");
    $bookClassParentUpdateClassStmt->bindParam(':id',     $id,     PDO::PARAM_STR);
    $bookClassParentUpdateClassStmt->bindParam(':parent', $parent, PDO::PARAM_STR);

    $bookClassParentUpdateNullStmt = $db->prepare("
        UPDATE `classes`
        SET `parent` = NULL
        WHERE `id` = :id
    ");
    $bookClassParentUpdateNullStmt->bindParam(':id', $id, PDO::PARAM_STR);

    $bookClassMethodInsertStmt = $db->prepare("
        INSERT INTO `classmethods`
            (`class_id`, `owner_class_id`, `slug`, `name`)
        VALUES
            (:class_id, :iowner_class_id, :islug, :name)
        ON DUPLICATE KEY UPDATE
            `owner_class_id` = :uowner_class_id,
            `slug` = :uslug,
            `last_seen` = :last_seen
    ");
    $bookClassMethodInsertStmt->bindParam(':class_id',        $class_id,       PDO::PARAM_INT);
    $bookClassMethodInsertStmt->bindParam(':iowner_class_id', $owner_class_id, PDO::PARAM_INT);
    $bookClassMethodInsertStmt->bindParam(':islug',           $slug,           PDO::PARAM_STR);
    $bookClassMethodInsertStmt->bindParam(':name',            $name,           PDO::PARAM_STR);
    $bookClassMethodInsertStmt->bindParam(':uowner_class_id', $owner_class_id, PDO::PARAM_INT);
    $bookClassMethodInsertStmt->bindParam(':uslug',           $slug,           PDO::PARAM_STR);
    $bookClassMethodInsertStmt->bindParam(':last_seen',       $last_seen,      PDO::PARAM_STR);

    $bookClassPropertyInsertStmt = $db->prepare("
        INSERT INTO `classprops`
            (`class_id`, `owner_class_id`, `slug`, `name`)
        VALUES
            (:class_id, :iowner_class_id, :islug, :name)
        ON DUPLICATE KEY UPDATE
            `owner_class_id` = :uowner_class_id,
            `slug` = :uslug,
            `last_seen` = :last_seen
    ");
    $bookClassPropertyInsertStmt->bindParam(':class_id',        $class_id,       PDO::PARAM_INT);
    $bookClassPropertyInsertStmt->bindParam(':iowner_class_id', $owner_class_id, PDO::PARAM_INT);
    $bookClassPropertyInsertStmt->bindParam(':islug',           $slug,           PDO::PARAM_STR);
    $bookClassPropertyInsertStmt->bindParam(':name',            $name,           PDO::PARAM_STR);
    $bookClassPropertyInsertStmt->bindParam(':uowner_class_id', $owner_class_id, PDO::PARAM_INT);
    $bookClassPropertyInsertStmt->bindParam(':uslug',           $slug,           PDO::PARAM_STR);
    $bookClassPropertyInsertStmt->bindParam(':last_seen',       $last_seen,      PDO::PARAM_STR);

    $bookClassConstantInsertStmt = $db->prepare("
        INSERT INTO `classconstants`
            (`class_id`, `owner_class_id`, `slug`, `name`)
        VALUES
            (:class_id, :iowner_class_id, :islug, :name)
        ON DUPLICATE KEY UPDATE
            `owner_class_id` = :uowner_class_id,
            `slug` = :uslug,
            `last_seen` = :last_seen
    ");
    $bookClassConstantInsertStmt->bindParam(':class_id',        $class_id,       PDO::PARAM_INT);
    $bookClassConstantInsertStmt->bindParam(':iowner_class_id', $owner_class_id, PDO::PARAM_INT);
    $bookClassConstantInsertStmt->bindParam(':islug',           $slug,           PDO::PARAM_STR);
    $bookClassConstantInsertStmt->bindParam(':name',            $name,           PDO::PARAM_STR);
    $bookClassConstantInsertStmt->bindParam(':uowner_class_id', $owner_class_id, PDO::PARAM_INT);
    $bookClassConstantInsertStmt->bindParam(':uslug',           $slug,           PDO::PARAM_STR);
    $bookClassConstantInsertStmt->bindParam(':last_seen',       $last_seen,      PDO::PARAM_STR);

    $cleanupStmts = [
        $db->prepare("
            DELETE FROM `books`
            WHERE `last_seen` < :last_seen
        ")
    ];
} catch (PDOException $e) {
    echo "Failed\n";
    fatal_error("Caught PDOException: " . $e->getMessage());
}
echo "OK\n\n";
*/

// Set up the DOM
echo "Loading manual XML... ";
$doc = new \DOMDocument;
if (!$doc->load($tempFile)) {
    echo "Failed\n";
    fatal_error("Unable to load manual XML");
}
echo "OK\n\n";

// Let's do some indexing!
echo "Indexing manual\n";

$xpath = new ManualXPath(new \DOMXPath($doc));

$bookRegistry = new BookRegistry;
$classRegistry = new ClassRegistry;

$bookBuilder = new BookBuilder($bookRegistry, new BookFactory, $xpath);
$configOptionBuilder = new ConfigOptionBuilder(new ConfigOptionFactory, $xpath);
$constantBuilder = new ConstantBuilder(new ConstantFactory, $xpath);
$functionBuilder = new FunctionBuilder(new FunctionFactory, $xpath);
$classBuilder = new ClassBuilder($classRegistry, new ClassFactory, new ClassMemberFactory, $xpath);

foreach ($xpath->query('//db:book[starts-with(@xml:id, "book.")]') as $bookEl) {
    $book = $bookBuilder->build($bookEl);

    echo "Indexing book " . $book->getSlug() . " (" . $book->getName() . ")... ";

    // Config options
    $query = ".//db:section[@xml:id='" . $book->getSlug() . ".configuration']//db:varlistentry[@xml:id]";
    foreach ($xpath->query($query, $bookEl) as $varListEntry) {
        $book->addGlobalSymbol($configOptionBuilder->build($varListEntry));
    }

    // Constants
    $query = ".//db:appendix[@xml:id='" . $book->getSlug() . ".constants']//db:varlistentry[@xml:id]";
    foreach ($xpath->query($query, $bookEl) as $varListEntry) {
        $book->addGlobalSymbol($constantBuilder->build($varListEntry));
    }

    // Functions
    $query = ".//db:reference[@xml:id='ref." . $book->getSlug() . "']//db:refentry[starts-with(@xml:id, 'function.')]";
    foreach ($xpath->query($query, $bookEl) as $refEntry) {
        $book->addGlobalSymbol($constantBuilder->build($refEntry));
    }

    // Classes
    $query = ".//pd:classref | .//pd:exceptionref";
    foreach ($xpath->query($query, $bookEl) as $classRef) {
        $book->addGlobalSymbol($classBuilder->build($classRef));
    }

    echo "OK\n";
}

$book_id = null;

/*
echo "Indexing control structures... ";
$sections = $xpath->query(".//db:section[@xml:id='language.control-structures']//db:sect1[@xml:id]/db:title/db:literal/../..");
foreach ($sections as $sect) {
    $slug = $sect->getAttribute('xml:id');
    $name = $type = '';

    $nameNodes = $xpath->query("./db:title/db:literal", $sect);
    foreach ($nameNodes as $nameNode) {
        if (preg_match('/^\S+$/', trim($nameNode->textContent), $match)) {
            $name = $match[0];
            $controlStructInsertStmt->execute();
            break;
        }
    }
}
echo "OK\n";
*/


echo "Indexing error constants... ";
$orphanedConstants = [];
foreach ($xpath->query(".//db:appendix[@xml:id='errorfunc.constants']//db:row[@xml:id]") as $row) {
    $orphanedConstants[] = $constantBuilder->build($row);
}
echo "OK\n";



echo "Indexing configuration options with no owner book... ";
$orphanedConfigOptions = [];
foreach ($xpath->query(".//db:section[@xml:id='ini.core']//db:varlistentry[@xml:id]") as $varListEntry) {
    $orphanedConfigOptions[] = $configOptionBuilder->build($varListEntry);
}
echo "OK\n";



echo "Indexing classes with no owner book... ";
$classRefs = $xpath->query(".//pd:classref | .//pd:exceptionref");
foreach ($classRefs as $classRef) {
    $classBuilder->build($classRef);
}
echo "OK\n";



echo "Storing class members... ";
foreach ($classRegistry as $class) {
    $class_id = $id = $class->getId();

    if ($class->hasParent()) {
        $parent = $class->getParent()->getId();
        $bookClassParentUpdateClassStmt->execute();
    } else {
        $bookClassParentUpdateNullStmt->execute();
    }

    foreach ($class->getMethods() as $method) {
        $owner_class_id = $method->ownerClass->getId();
        $slug = $method->slug;
        $name = $method->name;

        $bookClassMethodInsertStmt->execute();
    }

    foreach ($class->getProperties() as $property) {
        $owner_class_id = $property->ownerClass->getId();
        $slug = $property->slug;
        $name = $property->name;

        $bookClassPropertyInsertStmt->execute();
    }

    foreach ($class->getConstants() as $constant) {
        $owner_class_id = $constant->ownerClass->getId();
        $slug = $constant->slug;
        $name = $constant->name;

        $bookClassConstantInsertStmt->execute();
    }
}
echo "OK\n";

echo "\n";

// Remove temp file
//unlink($tempFile);
