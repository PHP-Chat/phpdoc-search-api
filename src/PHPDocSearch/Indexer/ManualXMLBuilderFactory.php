<?php

namespace PHPDocSearch\Indexer;

use PHPDocSearch\Logger,
    PHPDocSearch\Environment,
    PHPDocSearch\GitRepositoryFactory;

class ManualXMLBuilderFactory
{
    /**
     * Create a new ManualXMLBuilder instance
     *
     * @param Environment $env
     * @param Logger $logger
     * @return ManualXMLBuilder
     */
    public function create(Environment $env, Logger $logger)
    {
        return new ManualXMLBuilder($env, new GitRepositoryFactory, new ManualXMLWrapperFactory, $logger);
    }
}
