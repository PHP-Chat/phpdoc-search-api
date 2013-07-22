<?php

function get_input($msg, $default = null)
{
    $result = null;

    do {
        if ($default === null) {
            echo "$msg: ";
        } else {
            echo "$msg ($default): ";
        }

        $input = rtrim(fgets(STDIN), "\r\n");

        if ($input !== '') {
            $result = $input;
        } else if ($default !== null) {
            $result = $default;
        }
    } while ($result === null);

    return $result;
}
function confirm($msg, $default = null)
{
    $result = null;

    do {
        if ($default === null) {
            echo "$msg y/n: ";
        } else if ($default) {
            echo "$msg Y/n: ";
        } else {
            echo "$msg y/N: ";
        }

        switch (strtolower(rtrim(fgets(STDIN), "\r\n"))) {
            case 'y':
                $result = true;
                break;
            case 'n':
                $result = false;
                break;
            default:
                if ($default !== null) {
                    $result = (bool) $default;
                }
                break;
        }
    } while ($result === null);

    return $result;
}
function get_password($msg)
{
    if ($isWin = (bool) preg_match('/^win/i', PHP_OS)) {
        echo "WARNING! When running on Windows your password will be shown in the console!\n";
    } else {
        system('stty -echo');
    }

    echo "$msg: ";
    $result = rtrim(fgets(STDIN), "\r\n");

    if (!$isWin) {
        system('stty -echo');
        echo "\n";
    }

    return $result;
}

function fatal_error($msg)
{
    fwrite(STDERR, "\nFATAL ERROR: " . $msg . "\n\n");
    exit(1);
}

function generate_password($length = 16)
{
    static $charGroups = [
        ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'],
        ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'],
        ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
        ['|', ',', '.', '/', '<', '>', '?', ';', ':', '@', '#', '~', '[', ']', '{', '}', '-', '_', '=', '+', '!', '$', '%', '^', '&', '*', '(', ')'],
    ];

    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $j = mt_rand(0, 3);
        $result .= $charGroups[$j][array_rand($charGroups[$j])];
    }

    return str_shuffle($result);
}

$repos = [
    'base' => 'https://github.com/salathe/phpdoc-base',
    'en'   => 'https://github.com/salathe/phpdoc-en',
    'api'  => 'https://github.com/PHP-Chat/phpdoc-search-api',
];

$dbSchema = [
    "
        CREATE USER '%user%'@'%host%'
        IDENTIFIED BY '%pass%'
    ",
    "
        GRANT USAGE
        ON *.*
        TO '%user%'@'%host%'
    ",
    "
        CREATE DATABASE IF NOT EXISTS `%dbname%`
    ",
    "
        GRANT ALL
        ON `%dbname%`.*
        TO '%user%'@'%host%'
    ",
    "
        USE `%dbname%`
    ",
    "
        CREATE TABLE `books`
        (
            `id`         INT          UNSIGNED NOT NULL AUTO_INCREMENT,
            `slug`       VARCHAR(255) NOT NULL,
            `full_name`  VARCHAR(255) NOT NULL,
            `short_name` VARCHAR(255) NOT NULL,
            `last_seen`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

            PRIMARY KEY        (`id`),
            UNIQUE KEY  `slug` (`slug`)
        )
        DEFAULT CHARSET=utf8
    ",
    "
        CREATE TABLE `constants`
        (
            `id`        INT          UNSIGNED NOT NULL AUTO_INCREMENT,
            `book_id`   INT          UNSIGNED DEFAULT NULL,
            `slug`      VARCHAR(255) NOT NULL,
            `name`      VARCHAR(255) NOT NULL,
            `type`      VARCHAR(32)  NOT NULL,
            `last_seen` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

            PRIMARY KEY             (`id`),
            UNIQUE KEY  `book-slug` (`book_id`, `slug`)
        )
        DEFAULT CHARSET=utf8
    ",
    "
        CREATE TABLE `functions`
        (
            `id`        INT          UNSIGNED NOT NULL AUTO_INCREMENT,
            `book_id`   INT          UNSIGNED DEFAULT NULL,
            `slug`      VARCHAR(255) NOT NULL,
            `name`      VARCHAR(255) NOT NULL,
            `last_seen` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

            PRIMARY KEY             (`id`),
            UNIQUE KEY  `book-slug` (`book_id`, `slug`)
        )
        DEFAULT CHARSET=utf8
    ",
    "
        CREATE TABLE `inisettings`
        (
            `id`        INT          UNSIGNED NOT NULL AUTO_INCREMENT,
            `book_id`   INT          UNSIGNED DEFAULT NULL,
            `slug`      VARCHAR(255) NOT NULL,
            `name`      VARCHAR(255) NOT NULL,
            `type`      VARCHAR(32)  NOT NULL,
            `last_seen` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

            PRIMARY KEY             (`id`),
            UNIQUE KEY  `book-slug` (`book_id`, `slug`)
        )
        DEFAULT CHARSET=utf8
    ",
    "
        CREATE TABLE `classes`
        (
            `id`        INT          UNSIGNED NOT NULL AUTO_INCREMENT,
            `book_id`   INT          UNSIGNED DEFAULT NULL,
            `slug`      VARCHAR(255) NOT NULL,
            `name`      VARCHAR(255) NOT NULL,
            `parent`    INT          UNSIGNED DEFAULT NULL,
            `last_seen` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

            PRIMARY KEY             (`id`),
            UNIQUE KEY  `book-slug` (`book_id`, `slug`),
            KEY         `parent`    (`parent`)
        )
        DEFAULT CHARSET=utf8
    ",
    "
        CREATE TABLE `classconstants`
        (
            `id`             INT          UNSIGNED NOT NULL AUTO_INCREMENT,
            `class_id`       INT          UNSIGNED NOT NULL,
            `owner_class_id` INT          UNSIGNED NOT NULL,
            `slug`           VARCHAR(255) NOT NULL,
            `name`           VARCHAR(255) NOT NULL,
            `last_seen`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

            PRIMARY KEY              (`id`),
            UNIQUE KEY  `class-name` (`class_id`, `name`)
        )
        DEFAULT CHARSET=utf8
    ",
    "
        CREATE TABLE `classprops`
        (
            `id`             INT          UNSIGNED NOT NULL AUTO_INCREMENT,
            `class_id`       INT          UNSIGNED NOT NULL,
            `owner_class_id` INT          UNSIGNED NOT NULL,
            `slug`           VARCHAR(255) NOT NULL,
            `name`           VARCHAR(255) NOT NULL,
            `last_seen`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

            PRIMARY KEY              (`id`),
            UNIQUE KEY  `class-name` (`class_id`, `name`)
        )
        DEFAULT CHARSET=utf8
    ",
    "
        CREATE TABLE `classmethods`
        (
            `id`             INT          UNSIGNED NOT NULL AUTO_INCREMENT,
            `class_id`       INT          UNSIGNED NOT NULL,
            `owner_class_id` INT          UNSIGNED NOT NULL,
            `slug`           VARCHAR(255) NOT NULL,
            `name`           VARCHAR(255) NOT NULL,
            `last_seen`      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,

            PRIMARY KEY              (`id`),
            UNIQUE KEY  `class-name` (`class_id`, `name`)
        )
        DEFAULT CHARSET=utf8
    ",
];

echo "\nThis script configures your environment for running the phpdoc indexing tool\n\n";

$installBase = get_input("Enter base installation directory", getcwd());

if ($dbSetup = confirm("Do you want to set up the database?", true)) {
    echo "\n";

    $dbHost = get_input('Enter the hostname or IP address of your MySQL server', 'localhost');
    $dbAdminUser = get_input('Enter a database username with administrative privileges', 'root');
    $dbAdminPass = get_password('Password');
    $dbGrantHost = $dbHost === 'localhost' ? 'localhost' : '%';
    echo "\n";

    $dbIndexDBName = get_input('Enter a database name for the phpdoc indexing tool', 'phpdoc');
    $dbIndexDBUser = get_input('Enter a username for the phpdoc indexing tool', $dbIndexDBName);
    $dbIndexDBPass = get_password("Enter a password for the $dbIndexDBName user or press enter to use a randomly generated password");
    $dbIndexDBPass = $dbIndexDBPass === '' ? generate_password() : $dbIndexDBPass;
    echo "\n";
}
echo "\n";

echo "Checking paths... ";
if (!is_dir($installBase)) {
    if (file_exists($installBase)) {
        echo "Failed\n";
        fatal_error("The specified base installation path already exists and is not a directory");
    } else if (!mkdir($installBase, 0755, true)) {
        echo "Failed\n";
        fatal_error("Creating base installation directory failed");
    }
}
$installBase = realpath($installBase);
if ($installBase === false) {
    echo "Failed\n";
    fatal_error("Cannot resovle real path of installation base directory");
}
foreach (array_keys($repos) as $dir) {
    if (is_dir("$installBase/$dir")) {
        echo "Failed\n";
        fatal_error("Directory '$dir' already exists within the installation base directory");
    }
}
echo "OK\n";

if ($dbSetup) {
    echo "Connecting to database... ";
    try {
        $db = new PDO("mysql:host=$dbHost", $dbAdminUser, $dbAdminPass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Failed\n";
        fatal_error('Unable to connect to database: ' . $e->getMessage());
    }
    echo "OK\n\n";
}

foreach ($repos as $repo => $url) {
    echo "Cloning repository '$repo'... ";
    exec('git clone -q "' . $url . '" "' . $installBase . DIRECTORY_SEPARATOR . $repo . '"', $output, $exitCode);
    if ($exitCode) {
        echo "Failed\n";
        fatal_error("Cloning repository from '$url' failed with error code $exitCode");
    }
    echo "OK\n";
}
echo "\n";

if (!$dbSetup) {
    echo "Done\n";
    exit(0);
}

echo "Connecting to database... ";
try {
    $db = new PDO("mysql:host=$dbHost", $dbAdminUser, $dbAdminPass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Failed\n";
    fatal_error('Unable to connect to database: ' . $e->getMessage());
}
echo "OK\n\n";

echo "Creating DB schema... ";
try {
    $substitutionVars = [
        'host'   => $dbGrantHost,
        'user'   => $dbIndexDBUser,
        'pass'   => $dbIndexDBPass,
        'dbname' => $dbIndexDBName,
    ];

    foreach ($dbSchema as $query) {
        $db->query(preg_replace_callback('/%(\w+)%/', function($match) use($db, $substitutionVars) {
            return isset($substitutionVars[$match[1]]) ? $substitutionVars[$match[1]] : $match[0];
        }, $query));
    }
} catch (PDOException $e) {
    echo "Failed\n";
    fatal_error('Caught PDOException: ' . $e->getMessage());
}
echo "OK\n\n";

$configFile = <<<PHP
<?php

    \$config['dbhost'] = '$dbHost';
    \$config['dbuser'] = '$dbIndexDBUser';
    \$config['dbpass'] = '$dbIndexDBPass';
    \$config['dbname'] = '$dbIndexDBName';

    \$config['staleage'] = 7; // days

PHP;

echo "Writing config file... ";
file_put_contents($installBase . '/config.php', $configFile);
echo "OK\n\n";

echo "Done\n";
