<?php

namespace PHPDocSearch;

class CLIEnvironment extends Environment
{
    private $args = [];

    private $startTime;

    public function __construct($baseDir, Config $config, array $argv)
    {
        $this->setBaseDir($baseDir);
        $this->config = $config;
        $this->argv = $argv;

        $this->startTime = new \DateTime('now');
        $this->parseArgv($argv);
    }

    private function parseArgv($argv)
    {
        $current = null;

        for ($i = 1, $l = count($argv); $i < $l; $i++) {
            if (substr($argv[$i], 0, 2) === '--') {
                $name = strtolower(substr($argv[$i], 2));

                if (strpos($name, '=') !== false) {
                    list($name, $value) = explode('=', $name, 2);
                    $this->args[$name] = $value;
                } else {
                    $this->args[$name] = true;
                }

                $current = &$this->args[$name];
            } else if ($current === true) {
                $current = $argv[$i];
            } else {
                throw new \RuntimeException('Invalid option: ' . $argv[$i]);
            }
        }
    }

    public function hasArg($name)
    {
        return isset($this->args[strtolower($name)]);
    }

    public function getArg($name, $defaultValue = null)
    {
        if (isset($this->args[$name = strtolower($name)])) {
            return $this->args[$name];
        }

        return $defaultValue;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }
}
