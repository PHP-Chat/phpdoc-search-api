<?php

use \PHPDocSearch\CLIEnvironment,
    \PHPDocSearch\PDOBuilder,
    \PHPDocSearch\Indexer\DataMapper,
    \PHPDocSearch\Indexer\ManualXMLWrapper,
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

$env = new CLIEnvironment(realpath(__DIR__ . '/../../'), $argv);

// Resolve paths
echo "Resolving paths... ";
$tempDir = $env->getBaseDir() . '/temp';
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

$docRepos = ['base', 'en'];
$repoSyncCommand = 'git pull -q origin master';
$repoCleanupCommands = ['git checkout -q .', 'git clean -fq'];

// Pull latest doc repositories
echo "Synchronising doc repositories\n";
$hasWork = false;
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

if (!$hasWork && !$env->hasArg('force')) {
    exit("No changes since last index run, nothing to do\n");
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

// Set up the DOM
echo "Loading manual XML... ";
$xpath = new ManualXMLWrapper($tempFile);
echo "OK\n\n";

$dataMapper = new DataMapper((new PDOBuilder)->build($env), $env);

$bookRegistry = new BookRegistry;
$classRegistry = new ClassRegistry;

$bookBuilder = new BookBuilder($bookRegistry, new BookFactory, $xpath);
$configOptionBuilder = new ConfigOptionBuilder(new ConfigOptionFactory, $xpath);
$constantBuilder = new ConstantBuilder(new ConstantFactory, $xpath);
$functionBuilder = new FunctionBuilder(new FunctionFactory, $xpath);
$classBuilder = new ClassBuilder($classRegistry, new ClassFactory, new ClassMemberFactory, $xpath);

// Let's do some indexing!
echo "Indexing manual\n";
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



echo "Indexing classes with no owner book... ";
$classRefs = $xpath->query(".//pd:classref | .//pd:exceptionref");
foreach ($classRefs as $classRef) {
    $classBuilder->build($classRef);
}
echo "OK\n";



echo "Storing error constants... ";
foreach ($xpath->query(".//db:appendix[@xml:id='errorfunc.constants']//db:row[@xml:id]") as $row) {
    $dataMapper->insertConstant($constantBuilder->build($row));
}
echo "OK\n";



echo "Storing configuration options with no owner book... ";
foreach ($xpath->query(".//db:section[@xml:id='ini.core']//db:varlistentry[@xml:id]") as $varListEntry) {
    $dataMapper->insertConfigOption($configOptionBuilder->build($varListEntry));
}
echo "OK\n";



// Try and free the memory DOM is using
$xpath->close();
unset($xpath, $bookBuilder, $configOptionBuilder, $constantBuilder, $functionBuilder, $classBuilder);



echo "Storing books... ";
foreach ($bookRegistry as $book) {
    $dataMapper->insertBook($book);
}
echo "OK\n";



echo "Storing classes... ";
foreach ($classRegistry as $class) {
    $dataMapper->insertClass($class);
}
echo "OK\n";



echo "\n";



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

// Remove temp file
//unlink($tempFile);
