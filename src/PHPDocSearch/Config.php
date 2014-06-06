<?php

namespace PHPDocSearch;

class Config
{
    /**
     * @var array
     */
    private $options;

    /**
     * Constructor
     *
     * Loads a configuration file from the specified base directory. The
     * file 'config.php' must exist in the directory.
     *
     * @param string $baseDir
     * @throws \RuntimeException
     */
    public function __construct($baseDir)
    {
        $configFile = $baseDir . '/config.php';
        if (!is_file($configFile)) {
            throw new \RuntimeException("Config file {$configFile} does not exist");
        }

        $config = [];
        /** @noinspection PhpIncludeInspection */
        require $configFile;
        $this->options = $config;
    }

    /**
     * Get the value of the named config option
     *
     * @param string $name
     * @return mixed
     */
    public function getOption($name)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        return null;
    }
}
