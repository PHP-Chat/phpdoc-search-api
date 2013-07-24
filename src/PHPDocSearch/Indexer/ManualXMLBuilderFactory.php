<?php

namespace PHPDocSearch\Indexer;

use PHPDocSearch\Logger,
    PHPDocSearch\Environment,
    PHPDocSearch\GitRepositoryFactory;

class ManualXMLBuilderFactory
{
    public function create(Environment $env, Logger $logger = null)
    {
        return new ManualXMLBuilder($env, new GitRepositoryFactory, new ManualXMLWrapperFactory, $logger);
    }
}
