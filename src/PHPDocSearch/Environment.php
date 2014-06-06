<?php

namespace PHPDocSearch;

abstract class Environment
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $baseDir;

    /**
     * Constructor
     *
     * @param string $baseDir
     * @param Config $config
     * @throws \RuntimeException
     */
    public function __construct($baseDir, Config $config)
    {
        $this->setBaseDir($baseDir);
        $this->config = $config;
    }

    /**
     * Set the base directory
     *
     * @param string $path
     * @throws \RuntimeException
     */
    public function setBaseDir($path)
    {
        if (false === $this->baseDir = realpath($path)) {
            throw new \RuntimeException('Specified base directory is not accessible');
        }
    }

    /**
     * Get the base directory
     *
     * @return string
     */
    public function getBaseDir()
    {
        return $this->baseDir;
    }

    /**
     * Get the configuration object
     *
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Determine whether the named argument is available
     *
     * @param string $name
     * @return bool
     */
    abstract public function hasArg($name);

    /**
     * Get the value of a named argument
     *
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    abstract public function getArg($name, $defaultValue = null);
}
