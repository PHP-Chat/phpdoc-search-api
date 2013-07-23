<?php

class ClassRegistry
{
    private $classes = [];

    private $pendingParents = [];

    private $pendingInterfaces = [];

    public function registerClass(ClassDef $class)
    {
        if (!isset($this->classes[$name = trim(strtolower($class->getName()))])) {
            $this->classes[$name] = $class;

            if (isset($this->pendingParents[$name])) {
                foreach ($this->pendingParents[$name] as $child) {
                    $child->setParent($class);
                }
            }

            if (isset($this->pendingInterfaces[$name])) {
                foreach ($this->pendingInterfaces[$name] as $implementor) {
                    $implementor->addInterface($class);
                }
            }

            unset($this->pendingParents[$name], $this->pendingInterfaces[$name]);
        }
    }

    public function registerParent(ClassDef $class, $parentName)
    {
        $name = trim(strtolower($parentName));

        if (isset($this->classes[$name])) {
            $class->setParent($this->classes[$name]);
        } else {
            if (!isset($this->pendingParents[$name])) {
                $this->pendingParents[$name] = [];
            }

            $this->pendingParents[$name][] = $class;
        }
    }

    public function registerInterface(ClassDef $class, $interfaceName)
    {
        $name = trim(strtolower($interfaceName));

        if (isset($this->classes[$name])) {
            $class->addInterface($this->classes[$name]);
        } else {
            if (!isset($this->pendingInterfaces[$name])) {
                $this->pendingInterfaces[$name] = [];
            }

            $this->pendingInterfaces[$name][] = $class;
        }
    }

    public function getClasses()
    {
        return $this->classes;
    }

    public function getClassByName($className)
    {
        if (isset($this->classes[$name = strtolower(trim($className))])) {
            return $this->classes[$name];
        }
    }

    public function isRegistered($className)
    {
        return isset($this->classes[strtolower(trim($className))]);
    }
}

class ClassDef
{
    private $isNormalised = false;

    private $id;

    private $slug;

    private $name;

    private $parent;

    private $interfaces = [];

    private $methods = [];

    private $properties = [];

    private $constants = [];

    private function inheritMembers(ClassDef $class)
    {
        foreach ($class->getMethods() as $method) {
            $this->addMethod($method);
        }

        foreach ($class->getProperties() as $property) {
            $this->addProperty($property);
        }

        foreach ($class->getConstants() as $constant) {
            $this->addConstant($constant);
        }

        foreach ($class->getInterfaces() as $interface) {
            $this->addInterface($interface);
        }
    }

    private function normaliseMembers()
    {
        if (!$this->isNormalised) {
            if ($this->hasParent()) {
                $this->inheritMembers($this->parent);
            }

            foreach ($this->interfaces as $interface) {
                $this->inheritMembers($interface);
            }

            $this->isNormalised = true;
        }
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setSlug($slug)
    {
        $this->slug = trim($slug);
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setName($name)
    {
        $this->name = trim($name);
    }

    public function getName()
    {
        return $this->name;
    }

    public function setParent(ClassDef $parent)
    {
        $this->parent = $parent;
    }

    public function hasParent()
    {
        return $this->parent !== null;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function addMethod(MethodDef $method)
    {
        if (!isset($this->methods[$methodName = strtolower($method->name)])) {
            $this->methods[$methodName] = $method;

            if ($method->ownerClass === null) {
                $method->ownerClass = $this;
            }
        }
    }

    public function getMethods()
    {
        $this->normaliseMembers();

        return $this->methods;
    }

    public function addProperty(PropDef $property)
    {
        if (!isset($this->properties[$propName = strtolower($property->name)])) {
            $this->properties[$propName] = $property;

            if ($property->ownerClass === null) {
                $property->ownerClass = $this;
            }
        }
    }

    public function getProperties()
    {
        $this->normaliseMembers();

        return $this->properties;
    }

    public function addConstant(ConstDef $constant)
    {
        if (!isset($this->constants[$constName = strtolower($constant->name)])) {
            $this->constants[$constName] = $constant;

            if ($constant->ownerClass === null) {
                $constant->ownerClass = $this;
            }
        }
    }

    public function getConstants()
    {
        $this->normaliseMembers();

        return $this->constants;
    }

    public function addInterface(ClassDef $interface)
    {
        if (!in_array($interface, $this->interfaces, true)) {
            $this->interfaces[] = $interface;
        }
    }

    public function getInterfaces()
    {
        $this->normaliseMembers();

        return $this->interfaces;
    }
}

class MethodDef
{
    public $slug;

    public $name;

    public $ownerClass;
}

class PropDef
{
    public $slug;

    public $name;

    public $ownerClass;
}

class ConstDef
{
    public $slug;

    public $name;

    public $ownerClass;
}

class ClassEntityFactory
{
    public function createClassDef()
    {
        return new ClassDef;
    }

    public function createMethodDef()
    {
        return new MethodDef;
    }

    public function createPropDef()
    {
        return new PropDef;
    }

    public function createConstDef()
    {
        return new ConstDef;
    }
}

class ClassDefBuilder
{
    private $classRegistry;

    private $classEntityFactory;

    public function __construct(ClassRegistry $classRegistry, ClassEntityFactory $classEntityFactory, DOMXPath $xpath)
    {
        $this->classRegistry = $classRegistry;
        $this->classEntityFactory = $classEntityFactory;
        $this->xpath = $xpath;
    }

    private function getMemberName($fqName)
    {
        $nameParts = preg_split('/(::|->)/', trim($fqName), -1, PREG_SPLIT_NO_EMPTY);
        return array_pop($nameParts);
    }

    private function processClassSynopsis(DOMElement $classEl, ClassDef $classDef)
    {
        $classDef->setSlug($classEl->getAttribute('xml:id'));

        $synopsisInfo = $this->xpath->query(".//db:classsynopsis/db:classsynopsisinfo", $classEl);
        if ($synopsisInfo->length) {
            $currentEl = $synopsisInfo->item(0)->firstChild;

            while ($currentEl !== null) {
                if ($currentEl instanceof DOMElement) {
                    switch (strtolower($currentEl->tagName)) {
                        case 'ooclass':
                            $modifier = $this->xpath->query('./db:modifier', $currentEl);
                            if ($modifier->length && trim(strtolower($modifier->item(0)->textContent)) === 'extends') {
                                $parent = $this->xpath->query('./db:classname', $currentEl);
                                if ($parent->length) {
                                    $this->classRegistry->registerParent($classDef, $parent->item(0)->textContent);
                                }
                            } else if ($classDef->getName() === null) {
                                $className = $this->xpath->query('./db:classname', $currentEl);
                                if ($className->length) {
                                    $classDef->setName($className->item(0)->textContent);
                                }
                            }
                            break;

                        case 'oointerface':
                            $interface = $this->xpath->query('./db:interfacename', $currentEl);
                            if ($interface->length) {
                                $this->classRegistry->registerInterface($classDef, $interface->item(0)->textContent);
                            }
                            break;
                    }
                }

                $currentEl = $currentEl->nextSibling;
            }
        }

        if ($classDef->getName() === null) {
            $className = $this->xpath->query(".//db:classsynopsis/db:ooclass/db:classname", $classEl);
            if ($className->length) {
                $classDef->setName($className->item(0)->textContent);
            } else {
                $titleAbbrev = $this->xpath->query("./db:titleabbrev", $classEl);
                if ($titleAbbrev->length) {
                    $classDef->setName($titleAbbrev->item(0)->textContent);
                }
            }
        }
    }

    private function processMethods(DOMElement $classEl, ClassDef $classDef)
    {
        $methodRefs = $this->xpath->query(".//db:refentry", $classEl);
        foreach ($methodRefs as $methodRef) {
            $methodDef = $this->classEntityFactory->createMethodDef();

            $methodRefName = $this->xpath->query(".//db:refnamediv/db:refname", $methodRef);
            if ($methodRefName->length) {
                $methodDef->name = $this->getMemberName($methodRefName->item(0)->textContent);
                $methodDef->slug = strtolower($methodRef->getAttribute('xml:id'));

                $classDef->addMethod($methodDef);
            }
        }
    }

    private function processPropertiesAndConstants(DOMElement $classEl, ClassDef $classDef)
    {
        $propConstRefs = $this->xpath->query(".//db:classsynopsis/db:fieldsynopsis", $classEl);
        foreach ($propConstRefs as $propConstRef) {
            $isConst = false;
            foreach ($this->xpath->query(".//db:modifier", $propConstRef) as $modifier) {
                if (trim(strtolower($modifier->textContent)) === 'const') {
                    $isConst = true;
                }
            }

            $varName = $this->xpath->query(".//db:varname[@linkend]", $propConstRef);
            if ($varName->length) {
                $name = $this->getMemberName($varName->item(0)->textContent);
                $slug = trim(strtolower($varName->item(0)->getAttribute('linkend')));

                if ($isConst) {
                    $constDef = $this->classEntityFactory->createConstDef();
                    $constDef->name = $name;
                    $constDef->slug = $slug;

                    $classDef->addConstant($constDef);
                } else {
                    $propDef = $this->classEntityFactory->createPropDef();
                    $propDef->name = $name;
                    $propDef->slug = $slug;

                    $classDef->addProperty($propDef);
                }
            }
        }
    }

    public function build(DOMElement $classEl)
    {
        $classDef = $this->classEntityFactory->createClassDef();

        $this->processClassSynopsis($classEl, $classDef);

        if (!$this->classRegistry->isRegistered($classDef->getName())) {
            $this->processMethods($classEl, $classDef);
            $this->processPropertiesAndConstants($classEl, $classDef);

            $this->classRegistry->registerClass($classDef);

            return $classDef;
        }
    }
}

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
$now = new DateTime;
$last_seen = $now->format('Y-m-d H:i:s');
$stale_age = $now->modify("-{$config['staleage']} days")->format('Y-m-d H:i:s');

$hasWork = false;

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
    $db = new PDO("mysql:host={$config['dbhost']};dbname={$config['dbname']};charset=utf8", $config['dbuser'], $config['dbpass']);
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
$xpath->registerNamespace('php', 'http://php.net/xpath');
$xpath->registerPHPFunctions();
echo "OK\n\n";

// Let's do some indexing!
echo "Indexing manual\n";
$classRegistry = new ClassRegistry();
$classDefBuilder = new ClassDefBuilder($classRegistry, new ClassEntityFactory, $xpath);

$books = $xpath->query('//db:book[starts-with(@xml:id, "book.")]');
$classDefs = $pendingParents = [];
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
    $configOptions = $xpath->query(".//db:section[@xml:id='$bookSlug.configuration']//db:varlistentry[@xml:id]", $book);
    foreach ($configOptions as $opt) {
        $slug = $opt->getAttribute('xml:id');
        $name = $type = '';

        $nameNodes = $xpath->query("./db:term/db:parameter", $opt);
        if ($nameNodes->length) {
            $name = trim($nameNodes->item(0)->textContent);
        }

        $typeNodes = $xpath->query("./db:term/db:type", $opt);
        if ($typeNodes->length) {
            $type = trim($typeNodes->item(0)->textContent);
        }

        $bookIniInsertStmt->execute();
    }

    // Constants
    $constants = $xpath->query(".//db:appendix[@xml:id='$bookSlug.constants']//db:varlistentry[@xml:id]", $book);
    foreach ($constants as $const) {
        $slug = $const->getAttribute('xml:id');
        $name = $type = '';

        $nameNodes = $xpath->query("./db:term/db:constant", $const);
        if ($nameNodes->length) {
            $name = trim($nameNodes->item(0)->textContent);
        }

        $typeNodes = $xpath->query("./db:term/db:type", $const);
        if ($typeNodes->length) {
            $type = trim($typeNodes->item(0)->textContent);
        }

        $bookConstInsertStmt->execute();
    }

    // Functions
    $functions = $xpath->query(".//db:reference[@xml:id='ref.$bookSlug']//db:refentry[starts-with(@xml:id, 'function.')]", $book);
    foreach ($functions as $func) {
        $slug = $func->getAttribute('xml:id');

        $name = $xpath->query("./db:refnamediv/db:refname", $func)->item(0)->firstChild->data;

        $bookFuncInsertStmt->execute();
    }

    // Classes
    $classRefs = $xpath->query(".//pd:classref | .//pd:exceptionref", $book);
    foreach ($classRefs as $classRef) {
        if ($classDef = $classDefBuilder->build($classRef)) {
            $name = $classDef->getName();
            $slug = $classDef->getSlug();
            $bookClassInsertStmt->execute();

            $classDef->setId($db->lastInsertId());
        }
    }

    echo "OK\n";
}

$book_id = null;

echo "Indexing error constants... ";
$rows = $xpath->query(".//db:appendix[@xml:id='errorfunc.constants']//db:row[@xml:id]");
foreach ($rows as $row) {
    $slug = $row->getAttribute('xml:id');
    $name = $type = '';

    $nameNodes = $xpath->query("./db:entry/db:constant", $row);
    if ($nameNodes->length) {
        $name = trim($nameNodes->item(0)->textContent);
    }

    $typeNodes = $xpath->query("./db:entry/db:type", $row);
    if ($typeNodes->length) {
        $type = trim($typeNodes->item(0)->textContent);
    }

    $bookConstInsertStmt->execute();
}
echo "OK\n";

echo "Indexing configuration options with no owner book... ";
$varListEntries = $xpath->query(".//db:section[@xml:id='ini.core']//db:varlistentry[@xml:id]");
foreach ($varListEntries as $varListEntry) {
    $slug = $varListEntry->getAttribute('xml:id');
    $name = $type = '';

    $nameNodes = $xpath->query("./db:term/db:parameter", $varListEntry);
    if ($nameNodes->length) {
        $name = trim($nameNodes->item(0)->textContent);
    }

    $typeNodes = $xpath->query("./db:term/db:type", $varListEntry);
    if ($typeNodes->length) {
        $type = trim($typeNodes->item(0)->textContent);
    }

    $bookIniInsertStmt->execute();
}
echo "OK\n";

echo "Indexing classes with no owner book... ";
$classRefs = $xpath->query(".//pd:classref | .//pd:exceptionref");
foreach ($classRefs as $classRef) {
    if ($classDef = $classDefBuilder->build($classRef)) {
        $name = $classDef->getName();
        $slug = $classDef->getSlug();
        $bookClassInsertStmt->execute();

        $classDef->setId($db->lastInsertId());
    }
}
echo "OK\n";


echo "Storing class members... ";
foreach ($classRegistry->getClasses() as $class) {
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
