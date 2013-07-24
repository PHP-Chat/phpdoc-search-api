<?php

namespace PHPDocSearch\Indexer;

class ManualXMLWrapperFactory
{
    public function create($path, $keepFile)
    {
        return new ManualXMLWrapper($path, $keepFile);
    }
}
