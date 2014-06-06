<?php

namespace PHPDocSearch\Indexer;

class ManualXMLWrapperFactory
{
    /**
     * Create a new ManualXMLWrapper instance
     *
     * @param string $path
     * @param bool $keepFile
     * @return ManualXMLWrapper
     */
    public function create($path, $keepFile)
    {
        return new ManualXMLWrapper($path, $keepFile);
    }
}
