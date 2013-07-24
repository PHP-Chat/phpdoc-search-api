<?php

namespace PHPDocSearch;

class Environment
{
    private $config;

    protected $baseDir;

    public function getConfigOption($name)
    {
        if (!isset($this->config)) {
            $config = [];
            require $this->baseDir . '/config.php';
            $this->config = $config;
        }

        if (isset($this->config[$name])) {
            return $this->config[$name];
        }
    }

    public function getBaseDir()
    {
        return $this->baseDir;
    }
}
