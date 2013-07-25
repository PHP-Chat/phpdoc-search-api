<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Environment,
    \PHPDocSearch\Logger;

class Indexer
{
    private $env;

    private $bookRegistryFactory;

    private $classRegistryFactory;

    private $bookBuilder;

    private $configOptionBuilder;

    private $constantBuilder;

    private $functionBuilder;

    private $classBuilder;

    public function __construct(
        Environment $env,
        BookRegistryFactory $bookRegistryFactory,
        ClassRegistryFactory $classRegistryFactory,
        BookBuilder $bookBuilder,
        ConfigOptionBuilder $configOptionBuilder,
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
        $this->constantBuilder = $constantBuilder;
        $this->functionBuilder = $functionBuilder;
        $this->classBuilder = $classBuilder;
        $this->logger = $logger;
    }

    private function indexBooks($xmlWrapper, $bookRegistry, $classRegistry)
    {
        $this->logger->log('Indexing books...');

        foreach ($xmlWrapper->query('//db:book[starts-with(@xml:id, "book.")]') as $bookEl) {
            $book = $this->bookBuilder->build($bookEl, $xmlWrapper, $bookRegistry);

            $this->logger->log('Indexing book ' . $book->getName() . '...');

            $query = ".//db:section[@xml:id='" . $book->getSlug() . ".configuration']//db:varlistentry[@xml:id]";
            $count = 0;
            foreach ($xmlWrapper->query($query, $bookEl) as $varListEntry) {
                $book->addGlobalSymbol($this->configOptionBuilder->build($varListEntry, $xmlWrapper));
                $count++;
            }
            $this->logger->log("  $count config options");

            $query = ".//db:appendix[@xml:id='" . $book->getSlug() . ".constants']//db:varlistentry[@xml:id]";
            $count = 0;
            foreach ($xmlWrapper->query($query, $bookEl) as $varListEntry) {
                $book->addGlobalSymbol($this->constantBuilder->build($varListEntry, $xmlWrapper));
                $count++;
            }
            $this->logger->log("  $count constants");

            $query = ".//db:reference[@xml:id='ref." . $book->getSlug() . "']//db:refentry[starts-with(@xml:id, 'function.')]";
            $count = 0;
            foreach ($xmlWrapper->query($query, $bookEl) as $refEntry) {
                $book->addGlobalSymbol($this->functionBuilder->build($refEntry, $xmlWrapper));
                $count++;
            }
            $this->logger->log("  $count functions");

            $query = ".//pd:classref | .//pd:exceptionref";
            $count = 0;
            foreach ($xmlWrapper->query($query, $bookEl) as $classRef) {
                $book->addGlobalSymbol($this->classBuilder->build($classRef, $xmlWrapper, $classRegistry));
                $count++;
            }
            $this->logger->log("  $count classes");
        }
    }

    private function indexCoreClasses($xmlWrapper, $classRegistry)
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

    private function storeErrorConstants($xmlWrapper, $dataMapper)
    {
        $this->logger->log('Indexing/storing error constants...');

        $query = ".//db:appendix[@xml:id='errorfunc.constants']//db:row[@xml:id]";
        $count = 0;

        foreach ($xmlWrapper->query($query) as $row) {
            $dataMapper->insertConstant($this->constantBuilder->build($row, $xmlWrapper));
            $count++;
        }
        $this->logger->log("  $count entries found");
    }

    private function storeCoreConfigOptions($xmlWrapper, $dataMapper)
    {
        $this->logger->log('Indexing/storing core config options...');

        $query = ".//db:section[@xml:id='ini.core']//db:varlistentry[@xml:id]";
        $count = 0;

        foreach ($xmlWrapper->query($query) as $varListEntry) {
            $dataMapper->insertConfigOption($this->configOptionBuilder->build($varListEntry, $xmlWrapper));
            $count++;
        }

        $this->logger->log("  $count entries found");
    }

    private function storeBooks($bookRegistry, $dataMapper)
    {
        $this->logger->log('Storing books...');

        foreach ($bookRegistry as $book) {
            $dataMapper->insertBook($book);
        }
    }

    private function storeClasses($classRegistry, $dataMapper)
    {
        $this->logger->log('Storing classes...');

        foreach ($classRegistry as $class) {
            $dataMapper->insertClass($class);
        }
    }

    public function index(ManualXMLWrapper $xmlWrapper, DataMapper $dataMapper)
    {
        $bookRegistry = $this->bookRegistryFactory->create();
        $classRegistry = $this->classRegistryFactory->create();

        $this->indexBooks($xmlWrapper, $bookRegistry, $classRegistry);
        $this->indexCoreClasses($xmlWrapper, $classRegistry);

        $this->storeErrorConstants($xmlWrapper, $dataMapper);
        $this->storeCoreConfigOptions($xmlWrapper, $dataMapper);

        $xmlWrapper->close();

        $this->storeBooks($bookRegistry, $dataMapper);
        $this->storeClasses($classRegistry, $dataMapper);
    }
}
