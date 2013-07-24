<?php

namespace PHPDocSearch;

abstract class Environment
{
    protected $config;

    protected $baseDir;

    public function setBaseDir($path)
    {
        $this->baseDir = realpath($path);
    }

    public function getBaseDir()
    {
        return $this->baseDir;
    }

    public function getConfig()
    {
        return $this->config;
    }

    abstract public function hasArg($name);

    abstract public function getArg($name, $defaultValue = null);
}
