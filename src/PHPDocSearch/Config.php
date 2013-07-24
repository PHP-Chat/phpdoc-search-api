<?php

namespace PHPDocSearch;

class Config
{
    private $options;

    public function __construct($baseDir)
    {
        $configFile = $baseDir . '/config.php';
        if (!is_file($configFile)) {
            throw new \RuntimeException("Config file $configFile does not exist");
        }

        $config = [];
        require $configFile;
        $this->options = $config;
    }

    public function getOption($name)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }
    }
}
