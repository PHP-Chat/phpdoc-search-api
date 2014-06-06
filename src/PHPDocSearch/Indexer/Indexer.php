<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Environment,
    \PHPDocSearch\Logger;

class Indexer
{
    /**
     * @var Environment
     */
    private $env;

    /**
     * @var BookRegistryFactory
     */
    private $bookRegistryFactory;

    /**
     * @var ClassRegistryFactory
     */
    private $classRegistryFactory;

    /**
     * @var BookBuilder
     */
    private $bookBuilder;

    /**
     * @var ConfigOptionBuilder
     */
    private $configOptionBuilder;

    /**
     * @var ControlStructureBuilder
     */
    private $controlStructureBuilder;

    /**
     * @var MagicMethodBuilder
     */
    private $magicMethodBuilder;

    /**
     * @var ConstantBuilder
     */
    private $constantBuilder;

    /**
     * @var FunctionBuilder
     */
    private $functionBuilder;

    /**
     * @var ClassBuilder
     */
    private $classBuilder;

    /**
     * Constructor
     *
     * @param Environment $env
     * @param BookRegistryFactory $bookRegistryFactory
     * @param ClassRegistryFactory $classRegistryFactory
     * @param BookBuilder $bookBuilder
     * @param ConfigOptionBuilder $configOptionBuilder
     * @param ControlStructureBuilder $controlStructureBuilder
     * @param MagicMethodBuilder $magicMethodBuilder
     * @param ConstantBuilder $constantBuilder
     * @param FunctionBuilder $functionBuilder
     * @param ClassBuilder $classBuilder
     * @param Logger $logger
     */
    public function __construct(
        Environment $env,
        BookRegistryFactory $bookRegistryFactory,
        ClassRegistryFactory $classRegistryFactory,
        BookBuilder $bookBuilder,
        ConfigOptionBuilder $configOptionBuilder,
        ControlStructureBuilder $controlStructureBuilder,
        MagicMethodBuilder $magicMethodBuilder,
        ConstantBuilder $constantBuilder,
        FunctionBuilder $functionBuilder,
        ClassBuilder $classBuilder,
        Logger $logger
    ) {
        $this->env = $env;
        $this->bookRegistryFactory = $bookRegistryFactory;
        $this->classRegistryFactory = $classRegistryFactory;
        $this->bookBuilder = $bookBuilder;
        $this->configOptionBuilder = $configOptionBuilder;
        $this->controlStructureBuilder = $controlStructureBuilder;
        $this->magicMethodBuilder = $magicMethodBuilder;
        $this->constantBuilder = $constantBuilder;
        $this->functionBuilder = $functionBuilder;
        $this->classBuilder = $classBuilder;
        $this->logger = $logger;
    }

    /**
     * Index all books in the document
     *
     * @param ManualXMLWrapper $xmlWrapper
     * @param BookRegistry $bookRegistry
     * @param ClassRegistry $classRegistry
     */
    private function indexBooks(ManualXMLWrapper $xmlWrapper, BookRegistry $bookRegistry, ClassRegistry $classRegistry)
    {
        $this->logger->log('Indexing books...');

        foreach ($xmlWrapper->query('//db:book[starts-with(@xml:id, "book.")]') as $bookEl) {
            // Build this book object
            if (!$book = $this->bookBuilder->build($bookEl, $xmlWrapper, $bookRegistry)) {
                continue;
            }

            $this->logger->log('Indexing book ' . $book->getName() . '...');

            // Get config options
            $query = ".//db:section[@xml:id='" . $book->getSlug() . ".configuration']//db:varlistentry[@xml:id]";
            $count = 0;
            foreach ($xmlWrapper->query($query, $bookEl) as $varListEntry) {
                if ($configOption = $this->configOptionBuilder->build($varListEntry, $xmlWrapper)) {
                    $book->addGlobalSymbol($configOption);
                    $count++;
                }
            }
            $this->logger->log("  $count config options");

            // Get global constants
            $query = ".//db:appendix[@xml:id='" . $book->getSlug() . ".constants']//db:varlistentry[@xml:id]";
            $count = 0;
            foreach ($xmlWrapper->query($query, $bookEl) as $varListEntry) {
                if ($constant = $this->constantBuilder->build($varListEntry, $xmlWrapper)) {
                    $book->addGlobalSymbol($constant);
                    $count++;
                }
            }
            $this->logger->log("  $count constants");

            // Get global functions
            $query = ".//db:reference[@xml:id='ref." . $book->getSlug() . "']//db:refentry[starts-with(@xml:id, 'function.')]";
            $count = 0;
            foreach ($xmlWrapper->query($query, $bookEl) as $refEntry) {
                if ($function = $this->functionBuilder->build($refEntry, $xmlWrapper)) {
                    $book->addGlobalSymbol($function);
                    $count++;
                }
            }
            $this->logger->log("  $count functions");

            // Get global classes
            $query = ".//pd:classref | .//pd:exceptionref";
            $count = 0;
            foreach ($xmlWrapper->query($query, $bookEl) as $classRef) {
                if ($class = $this->classBuilder->build($classRef, $xmlWrapper, $classRegistry)) {
                    $book->addGlobalSymbol($class);
                    $count++;
                }
            }
            $this->logger->log("  $count classes");
        }
    }

    /**
     * Index classes that are not defined in a book
     *
     * @param ManualXMLWrapper $xmlWrapper
     * @param ClassRegistry $classRegistry
     */
    private function indexCoreClasses(ManualXMLWrapper $xmlWrapper, ClassRegistry $classRegistry)
    {
        $this->logger->log('Indexing core classes...');

        $query = ".//pd:classref | .//pd:exceptionref";
        $count = 0;
 
        foreach ($xmlWrapper->query($query) as $classRef) {
            $this->classBuilder->build($classRef, $xmlWrapper, $classRegistry);
            $count++;
        }
 
        $this->logger->log("  $count entries found");
    }

    /**
     * Index and store the error level constants
     *
     * @param ManualXMLWrapper $xmlWrapper
     * @param DataMapper $dataMapper
     */
    private function indexAndStoreErrorConstants(ManualXMLWrapper $xmlWrapper, DataMapper $dataMapper)
    {
        $this->logger->log('Indexing/storing error constants...');

        $query = ".//db:appendix[@xml:id='errorfunc.constants']//db:row[@xml:id]";
        $count = 0;

        foreach ($xmlWrapper->query($query) as $row) {
            if ($constant = $this->constantBuilder->build($row, $xmlWrapper)) {
                $dataMapper->insertConstant($constant);
                $count++;
            }
        }
        $this->logger->log("  $count entries found");
    }

    /**
     * Index and store config options that are not defined in a book
     *
     * @param ManualXMLWrapper $xmlWrapper
     * @param DataMapper $dataMapper
     */
    private function indexAndStoreCoreConfigOptions(ManualXMLWrapper $xmlWrapper, DataMapper $dataMapper)
    {
        $this->logger->log('Indexing/storing core config options...');

        $query = ".//db:section[@xml:id='ini.core']//db:varlistentry[@xml:id]";
        $count = 0;

        foreach ($xmlWrapper->query($query) as $varListEntry) {
            if ($configOption = $this->configOptionBuilder->build($varListEntry, $xmlWrapper)) {
                $dataMapper->insertConfigOption($configOption);
                $count++;
            }
        }

        $this->logger->log("  $count entries found");
    }

    /**
     * Index and store the language control structures
     *
     * @param ManualXMLWrapper $xmlWrapper
     * @param DataMapper $dataMapper
     */
    private function indexAndStoreControlStructures(ManualXMLWrapper $xmlWrapper, DataMapper $dataMapper)
    {
        $this->logger->log('Indexing/storing control structures...');

        $query = ".//db:chapter[@xml:id='language.control-structures']/db:sect1[@xml:id]";
        $count = 0;

        foreach ($xmlWrapper->query($query) as $sect) {
            if ($controlStructure = $this->controlStructureBuilder->build($sect, $xmlWrapper)) {
                $dataMapper->insertControlStructure($controlStructure);
                $count++;
            }
        }

        $this->logger->log("  $count entries found");
    }

    /**
     * Index and store the magic methods
     *
     * @param ManualXMLWrapper $xmlWrapper
     * @param DataMapper $dataMapper
     */
    private function indexAndStoreMagicMethods(ManualXMLWrapper $xmlWrapper, DataMapper $dataMapper)
    {
        $this->logger->log('Indexing/storing magic methods...');

        $query = ".//db:chapter[@xml:id='language.oop5']//db:methodsynopsis[starts-with(@xml:id, 'object.')]";
        $count = 0;

        foreach ($xmlWrapper->query($query) as $methodSynopsis) {
            if ($magicMethod = $this->magicMethodBuilder->build($methodSynopsis, $xmlWrapper)) {
                $dataMapper->insertMagicMethod($magicMethod);
                $count++;
            }
        }

        $this->logger->log("  $count entries found");
    }

    /**
     * Store the books from a registry
     *
     * @param BookRegistry $bookRegistry
     * @param DataMapper $dataMapper
     */
    private function storeBooks(BookRegistry $bookRegistry, DataMapper $dataMapper)
    {
        $this->logger->log('Storing books...');

        foreach ($bookRegistry as $book) {
            $dataMapper->insertBook($book);
        }
    }

    /**
     * Store the classes from a registry
     *
     * @param ClassRegistry $classRegistry
     * @param DataMapper $dataMapper
     */
    private function storeClasses(ClassRegistry $classRegistry, DataMapper $dataMapper)
    {
        $this->logger->log('Storing classes...');

        foreach ($classRegistry as $class) {
            $dataMapper->insertClass($class);
        }
    }

    /**
     * Index a manual XML document
     *
     * @param ManualXMLWrapper $xmlWrapper
     * @param DataMapper $dataMapper
     */
    public function index(ManualXMLWrapper $xmlWrapper, DataMapper $dataMapper)
    {
        $bookRegistry = $this->bookRegistryFactory->create();
        $classRegistry = $this->classRegistryFactory->create();

        $this->indexBooks($xmlWrapper, $bookRegistry, $classRegistry);
        $this->indexCoreClasses($xmlWrapper, $classRegistry);

        $this->indexAndStoreErrorConstants($xmlWrapper, $dataMapper);
        $this->indexAndStoreCoreConfigOptions($xmlWrapper, $dataMapper);
        $this->indexAndStoreControlStructures($xmlWrapper, $dataMapper);
        $this->indexAndStoreMagicMethods($xmlWrapper, $dataMapper);

        $xmlWrapper->close();

        $this->storeBooks($bookRegistry, $dataMapper);
        $this->storeClasses($classRegistry, $dataMapper);
    }
}
