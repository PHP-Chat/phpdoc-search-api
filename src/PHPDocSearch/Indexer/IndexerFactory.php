<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Environment,
    \PHPDocSearch\Logger,
    \PHPDocSearch\Symbols\BookFactory,
    \PHPDocSearch\Symbols\ClassFactory,
    \PHPDocSearch\Symbols\ClassMemberFactory,
    \PHPDocSearch\Symbols\ConfigOptionFactory,
    \PHPDocSearch\Symbols\ControlStructureFactory,
    \PHPDocSearch\Symbols\MagicMethodFactory,
    \PHPDocSearch\Symbols\ConstantFactory,
    \PHPDocSearch\Symbols\FunctionFactory;

class IndexerFactory
{
    /**
     * Create a new Indexer instance
     *
     * @param Environment $env
     * @param Logger $logger
     * @return Indexer
     */
    public function create(Environment $env, Logger $logger)
    {
        return new Indexer(
            $env,
            new BookRegistryFactory,
            new ClassRegistryFactory,
            new BookBuilder(new BookFactory),
            new ConfigOptionBuilder(new ConfigOptionFactory),
            new ControlStructureBuilder(new ControlStructureFactory),
            new MagicMethodBuilder(new MagicMethodFactory),
            new ConstantBuilder(new ConstantFactory),
            new FunctionBuilder(new FunctionFactory),
            new ClassBuilder(new ClassFactory, new ClassMemberFactory),
            $logger
        );
    }
}
