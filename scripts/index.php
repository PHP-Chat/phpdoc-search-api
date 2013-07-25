<?php

namespace PHPDocSearch;

use \PHPDocSearch\Indexer\DataMapper,
    \PHPDocSearch\Indexer\ManualXMLBuilderFactory,
    \PHPDocSearch\Indexer\IndexerFactory;


require __DIR__ . '/autoload.php';



try {
    $baseDir = realpath(__DIR__ . '/../../');
    $config = new Config($baseDir);
    $env = new CLIEnvironment($baseDir, $config, $argv);

    if ($env->hasArg('help')) {
        exit("

 PHP manual indexing tool

 Syntax:
   php index.php [option [option ...]]

 Options:
   --force      - Index even if no changes since last sync
   --help       - Display this help and exit
   --keep       - Do not delete the manual XML source after indexing
   --log <path> - Write log message to <path> (takes precedence over --quiet)
   --nobuild    - Don't rebuild the XML source (requires --keep on previous run)
   --nosync     - Don't sync with remote repositories (implies --force)
   --quiet      - No logging

");
}

    if ($env->hasArg('log')) {
        $logger = new FileLogger($env->getArg('log'));
    } else if ($env->hasArg('quiet')) {
        $logger = new BlackHoleLogger;
    } else {
        $logger = new CLILogger;
    }
} catch(\Exception $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}

set_exception_handler(function(\Exception $e) use($logger) {
    $logger->error($e->getMessage());
    exit(1);
});

$logger->log('Indexing process started, using ' . $baseDir . ' as base directory');

$xmlBuilder = (new ManualXMLBuilderFactory)->create($env, $logger);
$indexer = (new IndexerFactory)->create($env, $logger);

$dataMapper = new DataMapper($env, function() use($config) {
    $host = $config->getOption('db.host');
    $dbname = $config->getOption('db.name');
    $user = $config->getOption('db.user');
    $pass = $config->getOption('db.pass');
    $charset = 'utf8';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $db = new \PDO($dsn, $user, $pass);

    $db->setAttribute(\PDO::ATTR_ERRMODE,            \PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(\PDO::ATTR_EMULATE_PREPARES,   false);
    $db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

    return $db;
}, $logger);



$xmlWrapper = $xmlBuilder->build();
$indexer->index($xmlWrapper, $dataMapper);

$logger->log('Indexing process complete');
