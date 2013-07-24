<?php

namespace PHPDocSearch\Indexer;

use \PHPDocSearch\Environment,
    \PHPDocSearch\Logger,
    \PHPDocSearch\Symbols\BookFactory,
    \PHPDocSearch\Symbols\ClassFactory,
    \PHPDocSearch\Symbols\ClassMemberFactory,
    \PHPDocSearch\Symbols\ConfigOptionFactory,
    \PHPDocSearch\Symbols\ConstantFactory,
    \PHPDocSearch\Symbols\FunctionFactory;

class IndexerFactory
{
    public function create(Environment $env, Logger $logger = null)
    {
        return new Indexer(
            $env,
            new BookRegistryFactory,
            new ClassRegistryFactory,
            new BookBuilder(new BookFactory),
            new ConfigOptionBuilder(new ConfigOptionFactory),
            new ConstantBuilder(new ConstantFactory),
            new FunctionBuilder(new FunctionFactory),
            new ClassBuilder(new ClassFactory, new ClassMemberFactory),
            $logger
        );
    }
}
